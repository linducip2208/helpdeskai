<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Models\Ticket;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = Cache::remember('dashboard:stats', 120, fn () => $this->getStats());

        return view('admin.dashboard', [
            'totalTickets' => $stats['total_tickets'],
            'openTickets' => $stats['open_tickets'],
            'pendingTickets' => $stats['pending_tickets'],
            'resolvedTickets' => $stats['resolved_tickets'],
            'closedTickets' => $stats['closed_tickets'],
            'slaBreached' => $stats['sla_breached'],
            'unassignedTickets' => $stats['unassigned_tickets'],
            'ticketsToday' => $stats['tickets_today'],
            'ticketsThisWeek' => $stats['tickets_this_week'],
            'avgResponse' => $stats['avg_first_response'],
            'avgResolution' => $stats['avg_resolution'],
            'slaCompliance' => $stats['sla_compliance'],
            'csatAvg' => $stats['satisfaction_avg'],
            'aiCostMonth' => $stats['ai_usage_this_month'],
            'ticketTrends' => $stats['trends'],
            'byStatus' => $stats['by_status'],
            'byPriority' => $stats['by_priority'],
            'agentWorkload' => $stats['agent_workload'],
            'recentTickets' => Ticket::with(['user', 'assignedTo', 'department'])->latest()->take(8)->get(),
            'recentUsers' => User::latest()->take(6)->get(),
            'slaAtRisk' => Ticket::with(['assignedTo:id,name'])
                ->whereNotNull('sla_warned_at')
                ->where('sla_breached', false)
                ->whereIn('status', ['open', 'in_progress', 'waiting'])
                ->latest('sla_due_at')
                ->take(6)
                ->get(['id', 'uid', 'subject', 'assigned_to', 'sla_due_at']),
            'slaBreachedTop' => Ticket::with(['assignedTo:id,name'])
                ->where('sla_breached', true)
                ->whereIn('status', ['open', 'in_progress', 'waiting'])
                ->latest('sla_due_at')
                ->take(6)
                ->get(['id', 'uid', 'subject', 'assigned_to', 'sla_due_at']),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->getStats());
    }

    private function getStats(): array
    {
        // Shared scalars come from ReportService so dashboard and reports
        // can never disagree. Dashboard-only series stay local.
        $ov = app(ReportService::class)->overview();

        $openStatuses = ['open', 'in_progress', 'waiting'];

        $byStatus = Ticket::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $byPriority = Ticket::selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->all();

        $agentWorkload = Ticket::whereIn('status', $openStatuses)
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, COUNT(*) as total')
            ->groupBy('assigned_to')
            ->with('assignedTo:id,name')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        return [
            'total_tickets' => $ov['total_tickets'],
            'open_tickets' => $ov['open_tickets'],
            'pending_tickets' => Ticket::where('status', 'waiting')->count(),
            'resolved_tickets' => $ov['resolved_tickets'],
            'closed_tickets' => $ov['closed_tickets'],
            'sla_breached' => $ov['sla_breached'],
            'unassigned_tickets' => $ov['unassigned_tickets'],
            'total_users' => User::count(),
            'total_agents' => User::whereHas('roles', fn ($q) => $q->where('name', 'agent'))->count(),
            'avg_first_response' => $ov['avg_first_response'] ?? '—',
            'avg_resolution' => $ov['avg_resolution'] ?? '—',
            'sla_compliance' => $ov['sla_compliance'] !== null ? $ov['sla_compliance'].'%' : '—',
            'tickets_today' => Ticket::whereDate('created_at', today())->count(),
            'tickets_this_week' => Ticket::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'ai_usage_this_month' => (float) AiUsageLog::whereMonth('created_at', now()->month)->sum('cost_estimated'),
            'satisfaction_avg' => $ov['satisfaction_avg'],
            'trends' => $this->trends(14),
            'by_status' => $byStatus,
            'by_priority' => $byPriority,
            'agent_workload' => $agentWorkload,
        ];
    }

    /**
     * Ticket counts per day for the last N days (portable, no raw SQL date functions).
     *
     * @return array{labels: array<int,string>, values: array<int,int>}
     */
    private function trends(int $days): array
    {
        $since = now()->subDays($days - 1)->startOfDay();

        $dates = Ticket::where('created_at', '>=', $since)
            ->orderBy('id')
            ->pluck('created_at');

        $buckets = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $key = now()->subDays($i)->format('Y-m-d');
            $buckets[$key] = 0;
        }

        foreach ($dates as $date) {
            $key = $date->format('Y-m-d');
            if (array_key_exists($key, $buckets)) {
                $buckets[$key]++;
            }
        }

        return [
            'labels' => array_map(fn ($d) => date('M d', strtotime($d)), array_keys($buckets)),
            'values' => array_values($buckets),
        ];
    }
}

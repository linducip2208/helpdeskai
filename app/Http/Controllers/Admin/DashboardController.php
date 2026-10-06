<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = $this->getStats();

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
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->getStats());
    }

    private function getStats(): array
    {
        $openStatuses = ['open', 'in_progress', 'waiting'];

        $byStatus = Ticket::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $byPriority = Ticket::selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->all();

        $totalTickets = Ticket::count();
        $openTickets = Ticket::whereIn('status', $openStatuses)->count();
        $breached = Ticket::where('sla_breached', true)->count();
        $dueCount = Ticket::whereNotNull('sla_due_at')->count();

        $agentWorkload = Ticket::whereIn('status', $openStatuses)
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, COUNT(*) as total')
            ->groupBy('assigned_to')
            ->with('assignedTo:id,name')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        return [
            'total_tickets' => $totalTickets,
            'open_tickets' => $openTickets,
            'pending_tickets' => Ticket::where('status', 'waiting')->count(),
            'resolved_tickets' => Ticket::where('status', 'resolved')->count(),
            'closed_tickets' => Ticket::where('status', 'closed')->count(),
            'sla_breached' => $breached,
            'unassigned_tickets' => Ticket::whereIn('status', $openStatuses)->whereNull('assigned_to')->count(),
            'total_users' => User::count(),
            'total_agents' => User::whereHas('roles', fn ($q) => $q->where('name', 'agent'))->count(),
            'avg_first_response' => $this->formatDuration($this->avgMinutes('first_response_at')),
            'avg_resolution' => $this->formatDuration($this->avgMinutes('resolved_at')),
            'sla_compliance' => $dueCount > 0 ? round((($dueCount - $breached) / $dueCount) * 100, 1).'%' : '—',
            'tickets_today' => Ticket::whereDate('created_at', today())->count(),
            'tickets_this_week' => Ticket::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'ai_usage_this_month' => (float) AiUsageLog::whereMonth('created_at', now()->month)->sum('cost_estimated'),
            'satisfaction_avg' => round((float) Ticket::whereNotNull('satisfaction_rating')->avg('satisfaction_rating'), 1),
            'trends' => $this->trends(14),
            'by_status' => $byStatus,
            'by_priority' => $byPriority,
            'agent_workload' => $agentWorkload,
        ];
    }

    /**
     * Portable average of (column - created_at) in minutes over recent tickets.
     */
    private function avgMinutes(string $column): ?float
    {
        $rows = Ticket::whereNotNull($column)
            ->orderByDesc('id')
            ->take(500)
            ->get(['created_at', $column]);

        if ($rows->isEmpty()) {
            return null;
        }

        $total = $rows->sum(fn ($t) => max(0, $t->created_at->diffInMinutes($t->{$column})));

        return round($total / $rows->count(), 1);
    }

    private function formatDuration(?float $minutes): string
    {
        if ($minutes === null) {
            return '—';
        }

        if ($minutes < 60) {
            return $minutes.'m';
        }

        if ($minutes < 1440) {
            return round($minutes / 60, 1).'h';
        }

        return round($minutes / 1440, 1).'d';
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

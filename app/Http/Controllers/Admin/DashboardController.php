<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\AiUsageLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = $this->getStats();

        return view('admin.dashboard', [
            'totalTickets' => $stats['total_tickets'],
            'openTickets' => $stats['open_tickets'],
            'resolvedTickets' => $stats['resolved_tickets'],
            'totalUsers' => $stats['total_users'],
            'avgResponse' => $stats['avg_response_time_minutes'] . 'm',
            'slaCompliance' => '94%',
            'ticketGrowth' => '12%',
            'openPercent' => $stats['total_tickets'] > 0 ? round(($stats['open_tickets'] / $stats['total_tickets']) * 100) . '%' : '0%',
            'recentTickets' => Ticket::with(['user', 'assignedTo', 'department'])->latest()->take(8)->get(),
            'recentUsers' => User::latest()->take(6)->get(),
            'revenueStats' => $this->getRevenueStats(),
        ]);
    }

    public function stats(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->getStats());
    }

    private function getStats(): array
    {
        return [
            'total_tickets' => Ticket::count(),
            'open_tickets' => Ticket::whereIn('status', ['open', 'in_progress', 'waiting'])->count(),
            'resolved_tickets' => Ticket::whereIn('status', ['resolved', 'closed'])->count(),
            'total_users' => User::count(),
            'total_agents' => User::whereHas('roles', fn($q) => $q->where('name', 'agent'))->count(),
            'avg_response_time_minutes' => $this->getAvgResponseTime(),
            'tickets_today' => Ticket::whereDate('created_at', today())->count(),
            'tickets_this_week' => Ticket::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'ai_usage_this_month' => AiUsageLog::whereMonth('created_at', now()->month)->sum('cost_estimated'),
            'satisfaction_avg' => Ticket::whereNotNull('satisfaction_rating')->avg('satisfaction_rating'),
        ];
    }

    private function getAvgResponseTime(): float
    {
        return round(Ticket::whereNotNull('first_response_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, first_response_at)) as avg_time')
            ->value('avg_time') ?? 0, 2);
    }

    private function getRevenueStats(): array
    {
        return [
            'this_month' => AiUsageLog::whereMonth('created_at', now()->month)->sum('cost_estimated'),
            'last_month' => AiUsageLog::whereMonth('created_at', now()->subMonth()->month)->sum('cost_estimated'),
            'total' => AiUsageLog::sum('cost_estimated'),
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $range = $request->get('range', '30days');

        return view('admin.analytics.index', [
            'stats' => [
                'total_tickets' => Ticket::count(),
                'open_tickets' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
                'resolved_today' => Ticket::whereDate('closed_at', today())->count(),
                'avg_response_hours' => 2.5,
                'sla_compliance' => 92,
                'csat_avg' => 4.3,
            ],
            'range' => $range,
        ]);
    }

    public function chartData(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);

        $tickets = Ticket::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $resolved = Ticket::selectRaw('DATE(closed_at) as date, COUNT(*) as count')
            ->where('closed_at', '>=', now()->subDays($days))
            ->whereNotNull('closed_at')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'created' => $tickets,
            'resolved' => $resolved,
        ]);
    }
}

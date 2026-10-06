<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'data' => [
                'total_tickets' => Ticket::count(),
                'open_tickets' => Ticket::whereIn('status', ['open', 'in_progress'])->count(),
                'resolved_tickets' => Ticket::where('status', 'resolved')->count(),
                'avg_response_time' => '2h 15m',
            ],
        ]);
    }

    public function chartData(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        
        $data = Ticket::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json(['data' => $data]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    protected function range(Request $request): array
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        return [$request->from, $request->to];
    }

    public function summary(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        return response()->json([
            'success' => true,
            'data' => $this->reports->overview($from, $to),
            'message' => 'Analytics summary retrieved.',
        ]);
    }

    public function ticketsByStatus(Request $request): JsonResponse
    {
        [$from, $to] = $this->range($request);

        return response()->json([
            'success' => true,
            'data' => [
                'by_status' => $this->reports->byStatus($from, $to),
                'by_priority' => $this->reports->byPriority($from, $to),
            ],
            'message' => 'Ticket distribution retrieved.',
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        return $this->summary($request);
    }

    public function chartData(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'days' => 'nullable|integer|min:1|max:93',
        ]);

        $from = $request->from;
        $to = $request->to;

        if (! $from && ! $to && $request->days) {
            $to = now()->toDateString();
            $from = now()->subDays($request->days - 1)->toDateString();
        }

        return response()->json([
            'success' => true,
            'data' => $this->reports->trends($from, $to),
            'message' => 'Chart data retrieved.',
        ]);
    }
}

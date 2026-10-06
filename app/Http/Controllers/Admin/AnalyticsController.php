<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        [$normFrom, $normTo] = $this->reports->normalizeRange($from, $to);

        return view('admin.analytics.index', [
            'filters' => ['from' => substr($normFrom, 0, 10), 'to' => substr($normTo, 0, 10)],
            'overview' => $this->reports->overview($from, $to),
            'byStatus' => $this->reports->byStatus($from, $to),
            'byPriority' => $this->reports->byPriority($from, $to),
            'byDepartment' => $this->reports->byDepartment($from, $to),
            'byCategory' => $this->reports->byCategory($from, $to),
            'agentPerformance' => $this->reports->agentPerformance($from, $to),
            'trends' => $this->reports->trends($from, $to),
        ]);
    }

    public function chartData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'days' => 'nullable|integer|min:1|max:93',
        ]);

        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        if (! $from && ! $to && isset($validated['days'])) {
            $to = now()->toDateString();
            $from = now()->subDays($validated['days'] - 1)->toDateString();
        }

        return response()->json([
            'success' => true,
            'data' => $this->reports->trends($from, $to),
            'message' => 'Chart data retrieved.',
        ]);
    }
}

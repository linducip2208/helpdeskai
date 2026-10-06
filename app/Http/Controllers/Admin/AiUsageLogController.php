<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Models\AiUsageLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiUsageLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AiUsageLog::query()->with(['provider', 'model'])->latest();

        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }
        if ($request->filled('feature')) {
            $query->where('feature_key', $request->feature);
        }
        if ($request->filled('status')) {
            $query->where('success', $request->status === 'success');
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(50)->withQueryString();

        $total = AiUsageLog::count();
        $summary = [
            'total_requests' => $total,
            'success_rate' => $total ? round(AiUsageLog::where('success', true)->count() / $total * 100, 1) : 0,
            'total_cost' => (float) AiUsageLog::sum('cost_estimated'),
            'cost_this_month' => (float) AiUsageLog::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('cost_estimated'),
            'avg_latency_ms' => (int) AiUsageLog::avg('latency_ms'),
            'tokens_total' => (int) (AiUsageLog::sum('input_tokens') + AiUsageLog::sum('output_tokens')),
        ];

        return view('admin.ai-usage-logs.index', [
            'logs' => $logs,
            'summary' => $summary,
            'providers' => AiProvider::orderBy('name')->get(['id', 'name']),
            'features' => AiUsageLog::query()->select('feature_key')->distinct()->pluck('feature_key')->filter()->values(),
        ]);
    }
}

@extends('layouts.admin')
@section('title', 'AI Usage Logs')
@section('content')

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">AI Usage Logs</h2>
            <p class="text-sm text-slate-500 mt-1">Cost monitoring &amp; latency per request — semua call ke AI provider tercatat di sini.</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-slate-500">Total Requests</p>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($summary['total_requests']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-slate-500">Success Rate</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $summary['success_rate'] }}%</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-slate-500">Cost This Month</p>
            <p class="text-2xl font-bold text-indigo-600">${{ number_format($summary['cost_this_month'], 4) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-slate-500">Total Cost</p>
            <p class="text-2xl font-bold text-slate-900">${{ number_format($summary['total_cost'], 4) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-slate-500">Avg Latency</p>
            <p class="text-2xl font-bold text-amber-600">{{ number_format($summary['avg_latency_ms']) }}<span class="text-sm">ms</span></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs text-slate-500">Total Tokens</p>
            <p class="text-2xl font-bold text-slate-900">{{ number_format($summary['tokens_total']) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-100 p-4">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <select name="provider_id" class="rounded-lg border-gray-200 text-sm">
                <option value="">All providers</option>
                @foreach($providers as $p)
                    <option value="{{ $p->id }}" {{ request('provider_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
            <select name="feature" class="rounded-lg border-gray-200 text-sm">
                <option value="">All features</option>
                @foreach($features as $f)
                    <option value="{{ $f }}" {{ request('feature') == $f ? 'selected' : '' }}>{{ $f }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-lg border-gray-200 text-sm">
                <option value="">All</option>
                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success only</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed only</option>
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border-gray-200 text-sm">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border-gray-200 text-sm">
        </div>
        <div class="mt-3 flex gap-2">
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg">Filter</button>
            <a href="{{ route('admin.ai-usage-logs.index') }}" class="px-4 py-2 border border-gray-200 text-slate-700 text-sm rounded-lg hover:bg-gray-50">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Time</th>
                        <th class="px-4 py-3 text-left">Provider</th>
                        <th class="px-4 py-3 text-left">Model</th>
                        <th class="px-4 py-3 text-left">Feature</th>
                        <th class="px-4 py-3 text-right">In</th>
                        <th class="px-4 py-3 text-right">Out</th>
                        <th class="px-4 py-3 text-right">Cost</th>
                        <th class="px-4 py-3 text-right">Latency</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 text-slate-900">{{ $log->provider->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $log->model->model_name ?? '—' }}</td>
                            <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded">{{ $log->feature_key }}</span></td>
                            <td class="px-4 py-3 text-right">{{ number_format($log->input_tokens) }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($log->output_tokens) }}</td>
                            <td class="px-4 py-3 text-right font-medium">${{ number_format($log->cost_estimated, 6) }}</td>
                            <td class="px-4 py-3 text-right">{{ $log->latency_ms }}ms</td>
                            <td class="px-4 py-3 text-center">
                                @if($log->success)
                                    <span class="text-xs px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded">success</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-rose-50 text-rose-700 rounded" title="{{ $log->error_message }}">failed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-12 text-center text-slate-400">No AI usage logs yet. Run some AI features first.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
        @endif
    </div>
</div>

@endsection

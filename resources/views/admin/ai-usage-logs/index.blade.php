@extends('layouts.admin')
@section('title', 'AI Usage Logs')
@section('content')

<p class="text-muted mb-3">Cost monitoring &amp; latency per request — semua call ke AI provider tercatat di sini.</p>

<div class="row row-cards mb-3">
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Total Requests</div>
                <div class="h2 mb-0">{{ number_format($summary['total_requests']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Success Rate</div>
                <div class="h2 mb-0 text-green">{{ $summary['success_rate'] }}%</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Cost This Month</div>
                <div class="h2 mb-0 text-blue">${{ number_format($summary['cost_this_month'], 4) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Total Cost</div>
                <div class="h2 mb-0">${{ number_format($summary['total_cost'], 4) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Avg Latency</div>
                <div class="h2 mb-0 text-yellow">{{ number_format($summary['avg_latency_ms']) }}<span class="h4">ms</span></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Total Tokens</div>
                <div class="h2 mb-0">{{ number_format($summary['tokens_total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Cost Today</div>
                <div class="h2 mb-0 text-blue">${{ number_format($summary['cost_today'], 4) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted">Fallbacks</div>
                <div class="h2 mb-0 text-yellow">{{ number_format($summary['fallbacks']) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mb-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Provider Performance</h3></div>
            <div class="table-responsive"><table class="table table-vcenter card-table">
                <thead><tr><th>Provider</th><th>Requests</th><th>Success</th><th>Tokens</th><th>Cost</th><th>Avg ms</th></tr></thead>
                <tbody>
                    @forelse($byProvider ?? [] as $row)
                    <tr>
                        <td>{{ $row->provider->name ?? '—' }}</td>
                        <td class="text-muted">{{ number_format($row->requests) }}</td>
                        <td class="text-muted">{{ $row->requests ? round($row->succeeded / $row->requests * 100, 1) : 0 }}%</td>
                        <td class="text-muted">{{ number_format($row->tokens) }}</td>
                        <td class="text-muted">${{ number_format($row->cost, 4) }}</td>
                        <td class="text-muted">{{ number_format($row->latency) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6"><div class="empty"><p class="empty-title">No data yet.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Top Models &amp; Budgets</h3></div>
            <div class="table-responsive"><table class="table table-vcenter card-table">
                <thead><tr><th>Model</th><th>Requests</th><th>Success</th><th>Cost</th></tr></thead>
                <tbody>
                    @forelse($byModel ?? [] as $row)
                    <tr>
                        <td class="font-monospace small">{{ $row->model->model_id ?? '—' }}</td>
                        <td class="text-muted">{{ number_format($row->requests) }}</td>
                        <td class="text-muted">{{ $row->requests ? round($row->succeeded / $row->requests * 100, 1) : 0 }}%</td>
                        <td class="text-muted">${{ number_format($row->cost, 4) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4"><div class="empty"><p class="empty-title">No data yet.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
            @if(! empty($budgets))
            <div class="card-body border-top">
                <div class="subheader mb-2">Budgets</div>
                @foreach($budgets as $label => $b)
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">{{ $label }}</span>
                    <span class="small">${{ number_format($b['spent'], 4) }} / ${{ number_format($b['limit'], 2) }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ url()->current() }}">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="provider_id" class="form-select">
                        <option value="">All providers</option>
                        @foreach($providers as $p)
                            <option value="{{ $p->id }}" {{ request('provider_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="feature" class="form-select">
                        <option value="">All features</option>
                        @foreach($features as $f)
                            <option value="{{ $f }}" {{ request('feature') == $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success only</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed only</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.ai-usage-logs.index') }}" class="btn">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Provider</th>
                    <th>Model</th>
                    <th>Feature</th>
                    <th class="text-end">In</th>
                    <th class="text-end">Out</th>
                    <th class="text-end">Cost</th>
                    <th class="text-end">Latency</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-muted text-nowrap">{{ wib($log->created_at) }}</td>
                        <td>{{ $log->provider->name ?? '—' }}</td>
                        <td><code>{{ $log->model->model_name ?? '—' }}</code></td>
                        <td><span class="badge bg-blue-lt">{{ $log->feature_key }}</span></td>
                        <td class="text-end">{{ number_format($log->input_tokens) }}</td>
                        <td class="text-end">{{ number_format($log->output_tokens) }}</td>
                        <td class="text-end">${{ number_format($log->cost_estimated, 6) }}</td>
                        <td class="text-end">{{ $log->latency_ms }}ms</td>
                        <td class="text-center">
                            @if($log->success)
                                <span class="badge bg-green-lt">success</span>
                            @else
                                <span class="badge bg-red-lt" title="{{ $log->error_message }}">failed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><div class="empty"><p class="empty-title">No AI usage logs yet.</p><p class="empty-subtitle text-muted">Run some AI features first.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-center">{{ $logs->links() }}</div>
    @endif
</div>

@endsection

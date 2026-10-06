@extends('layouts.admin')
@section('title', __('Analytics'))
@section('page-actions')
    <div class="dropdown">
        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown">{{ __('Export CSV') }}</button>
        <div class="dropdown-menu dropdown-menu-end">
            <a href="{{ route('admin.export.tickets', request()->only(['from','to'])) }}" class="dropdown-item">{{ __('Export Tickets CSV') }}</a>
            <a href="{{ route('admin.export.sla', request()->only(['from','to'])) }}" class="dropdown-item">{{ __('Export SLA CSV') }}</a>
            <a href="{{ route('admin.export.ai-usage', request()->only(['from','to'])) }}" class="dropdown-item">{{ __('Export AI Usage CSV') }}</a>
        </div>
    </div>
    <div class="dropdown">
        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">{{ __('Export XLSX') }}</button>
        <div class="dropdown-menu dropdown-menu-end">
            <a href="{{ route('admin.export.tickets.xlsx', request()->only(['from','to'])) }}" class="dropdown-item">{{ __('Export Tickets XLSX') }}</a>
            <a href="{{ route('admin.export.sla.xlsx', request()->only(['from','to'])) }}" class="dropdown-item">{{ __('Export SLA XLSX') }}</a>
            <a href="{{ route('admin.export.ai-usage.xlsx', request()->only(['from','to'])) }}" class="dropdown-item">{{ __('Export AI Usage XLSX') }}</a>
        </div>
    </div>
@endsection
@section('content')
@php
    $ov = $overview ?? [];
    $total = max(1, (int) ($ov['total_tickets'] ?? 0));
    $statusColors = ['open' => 'green', 'in_progress' => 'blue', 'waiting' => 'yellow', 'answered' => 'indigo', 'resolved' => 'purple', 'closed' => 'secondary'];
    $priorityColors = ['urgent' => 'red', 'high' => 'orange', 'medium' => 'yellow', 'low' => 'green'];
@endphp

<div class="card mb-3">
    <div class="card-body">
        <form method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">{{ __('From') }}</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('To') }}</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
                    <a href="{{ route('admin.analytics.index') }}" class="btn">{{ __('Reset') }}</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row row-cards mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card"><div class="card-body">
            <div class="subheader">{{ __('Total Tickets') }}</div>
            <div class="h1 mb-1">{{ $ov['total_tickets'] ?? 0 }}</div>
            <div class="text-muted small">{{ __(':reopened reopened', ['reopened' => $ov['reopened_tickets'] ?? 0]) }}</div>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card"><div class="card-body">
            <div class="subheader">{{ __('Open Tickets') }}</div>
            <div class="h1 mb-1">{{ $ov['open_tickets'] ?? 0 }}</div>
            <div class="text-muted small">{{ __(':resolved resolved / :closed closed', ['resolved' => $ov['resolved_tickets'] ?? 0, 'closed' => $ov['closed_tickets'] ?? 0]) }}</div>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card"><div class="card-body">
            <div class="subheader">{{ __('Avg First Response') }}</div>
            <div class="h1 mb-1">{{ $ov['avg_first_response'] ?? '—' }}</div>
            <div class="text-muted small">{{ __('Avg resolution: :time', ['time' => $ov['avg_resolution'] ?? '—']) }}</div>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card"><div class="card-body">
            <div class="subheader">{{ __('SLA Compliance') }}</div>
            <div class="h1 mb-1">{{ $ov['sla_compliance'] !== null ? $ov['sla_compliance'] . '%' : '—' }}</div>
            <div class="text-muted small">{{ __(':count breached / CSAT :csat', ['count' => $ov['sla_breached'] ?? 0, 'csat' => $ov['satisfaction_avg'] ?? '—']) }}</div>
        </div></div>
    </div>
</div>

<div class="row row-cards mb-3">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Created vs Resolved') }}</h3></div>
            <div class="card-body"><div id="chart-analytics-trends" style="min-height: 240px;"></div></div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('AI & Automation') }}</h3></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">{{ __('AI calls') }}</span><strong>{{ $ov['ai_calls'] ?? 0 }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted">{{ __('AI cost (est.)') }}</span><strong>${{ number_format($ov['ai_cost'] ?? 0, 4) }}</strong></div>
                <div class="d-flex justify-content-between"><span class="text-muted">{{ __('Automation fired') }}</span><strong>{{ $ov['automation_fired'] ?? 0 }}</strong></div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mb-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Tickets by Status') }}</h3></div>
            <div class="card-body">
                @forelse($byStatus ?? [] as $status => $count)
                @php $pct = round($count / $total * 100, 1); @endphp
                <div class="mb-3">
                    <div class="row align-items-center mb-1">
                        <div class="col">{{ ucfirst(str_replace('_', ' ', $status)) }}</div>
                        <div class="col-auto text-muted">{{ $count }} ({{ $pct }}%)</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-{{ $statusColors[$status] ?? 'primary' }}" style="width: {{ $pct }}%" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $status }}"></div>
                    </div>
                </div>
                @empty
                <div class="empty"><p class="empty-title">{{ __('No data available') }}</p></div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Tickets by Priority') }}</h3></div>
            <div class="card-body">
                @forelse($byPriority ?? [] as $priority => $count)
                @php $pct = round($count / $total * 100, 1); @endphp
                <div class="mb-3">
                    <div class="row align-items-center mb-1">
                        <div class="col">{{ ucfirst($priority) }}</div>
                        <div class="col-auto text-muted">{{ $count }} ({{ $pct }}%)</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-{{ $priorityColors[$priority] ?? 'primary' }}" style="width: {{ $pct }}%" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $priority }}"></div>
                    </div>
                </div>
                @empty
                <div class="empty"><p class="empty-title">{{ __('No data available') }}</p></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mb-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Tickets by Department') }}</h3></div>
            <div class="table-responsive"><table class="table table-vcenter card-table">
                <thead><tr><th>{{ __('Department') }}</th><th class="text-end">{{ __('Tickets') }}</th></tr></thead>
                <tbody>
                    @forelse($byDepartment ?? [] as $row)
                    <tr><td>{{ $row['name'] }}</td><td class="text-end text-muted">{{ $row['total'] }}</td></tr>
                    @empty
                    <tr><td colspan="2"><div class="empty"><p class="empty-title">{{ __('No data available') }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Tickets by Category') }}</h3></div>
            <div class="table-responsive"><table class="table table-vcenter card-table">
                <thead><tr><th>{{ __('Category') }}</th><th class="text-end">{{ __('Tickets') }}</th></tr></thead>
                <tbody>
                    @forelse($byCategory ?? [] as $row)
                    <tr><td>{{ $row['name'] }}</td><td class="text-end text-muted">{{ $row['total'] }}</td></tr>
                    @empty
                    <tr><td colspan="2"><div class="empty"><p class="empty-title">{{ __('No data available') }}</p></div></td></tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">{{ __('Agent Performance') }}</h3></div>
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>{{ __('Agent') }}</th>
                    <th>{{ __('Assigned') }}</th>
                    <th>{{ __('Resolved') }}</th>
                    <th>{{ __('Open') }}</th>
                    <th>{{ __('Avg Response') }}</th>
                    <th>{{ __('CSAT') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agentPerformance ?? [] as $perf)
                <tr>
                    <td><strong>{{ $perf['name'] }}</strong></td>
                    <td class="text-muted">{{ $perf['assigned'] }}</td>
                    <td class="text-muted">{{ $perf['resolved'] }}</td>
                    <td class="text-muted">{{ $perf['open'] }}</td>
                    <td class="text-muted">{{ $perf['avg_response'] ?? '—' }}</td>
                    <td class="text-muted">{{ $perf['csat'] ?: '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty">
                            <p class="empty-title">{{ __('No data available') }}</p>
                            <p class="empty-subtitle text-muted">{{ __('No agent performance data for this period.') }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    if (typeof window.ApexCharts === 'undefined') return;
    var el = document.querySelector('#chart-analytics-trends');
    if (!el) return;
    new window.ApexCharts(el, {
        chart: { type: 'area', height: 240, toolbar: { show: false }, fontFamily: 'inherit' },
        series: [
            { name: '{{ __('Created') }}', data: @json(($trends ?? [])['created'] ?? []) },
            { name: '{{ __('Resolved') }}', data: @json(($trends ?? [])['resolved'] ?? []) },
        ],
        xaxis: { categories: @json(($trends ?? [])['labels'] ?? []) },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: { enabled: false },
        colors: ['#066fd1', '#2fb344'],
        grid: { strokeDashArray: 4 },
    }).render();
})();
</script>
@endsection

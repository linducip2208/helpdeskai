@extends('layouts.admin')
@section('title', __('Dashboard'))
@section('content')

<div class="row row-cards">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">{{ __('Total Tickets') }}</div>
                </div>
                <div class="h1 mb-1">{{ $totalTickets ?? 0 }}</div>
                <div class="text-muted small">{{ __(':today today / :week this week', ['today' => $ticketsToday ?? 0, 'week' => $ticketsThisWeek ?? 0]) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">{{ __('Open Tickets') }}</div>
                <div class="h1 mb-1">{{ $openTickets ?? 0 }}</div>
                <div class="text-muted small">{{ __(':pending pending / :unassigned unassigned', ['pending' => $pendingTickets ?? 0, 'unassigned' => $unassignedTickets ?? 0]) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">{{ __('Avg First Response') }}</div>
                <div class="h1 mb-1">{{ $avgResponse ?? '—' }}</div>
                <div class="text-muted small">{{ __('Avg resolution: :time', ['time' => $avgResolution ?? '—']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="subheader">{{ __('SLA Compliance') }}</div>
                <div class="h1 mb-1">{{ $slaCompliance ?? '—' }}</div>
                <div class="text-muted small">{{ __(':count breached / CSAT :csat', ['count' => $slaBreached ?? 0, 'csat' => $csatAvg ?? '—']) }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Ticket Trends (14 days)') }}</h3>
            </div>
            <div class="card-body">
                <div id="chart-trends" style="min-height: 240px;"></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Tickets by Status') }}</h3>
            </div>
            <div class="card-body">
                <div id="chart-status" style="min-height: 240px;"></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Recent Tickets') }}</h3>
                <div class="card-actions">
                    <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm">{{ __('View all') }}</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>{{ __('Subject') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Priority') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTickets ?? [] as $ticket)
                        @php $st = (string) ($ticket->status?->value ?? $ticket->status); @endphp
                        <tr>
                            <td><a href="{{ route('admin.tickets.show', $ticket) }}">{{ $ticket->subject }}</a></td>
                            <td>
                                <span class="badge {{ in_array($st, ['open', 'resolved', 'answered']) ? 'bg-green-lt' : ($st === 'in_progress' ? 'bg-blue-lt' : ($st === 'closed' ? 'bg-secondary' : 'bg-yellow-lt')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $st)) }}
                                </span>
                            </td>
                            <td class="text-muted">{{ ucfirst($ticket->priority ?? 'medium') }}</td>
                            <td class="text-muted">{{ wib($ticket->created_at, 'd F Y', false) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4"><div class="empty"><p class="empty-title">{{ __('No tickets yet.') }}</p></div></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Agent Workload') }}</h3>
                <div class="card-actions">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm">{{ __('View all') }}</a>
                </div>
            </div>
            <div class="card-body">
                @forelse($agentWorkload ?? [] as $row)
                <div class="row align-items-center mb-2">
                    <div class="col-auto">
                        <span class="avatar avatar-sm">{{ strtoupper(substr($row->assignedTo->name ?? '?', 0, 1)) }}</span>
                    </div>
                    <div class="col">
                        <div>{{ $row->assignedTo->name ?? __('Unassigned') }}</div>
                        <div class="progress progress-sm">
                            @php $pct = ($openTickets ?? 0) > 0 ? min(100, round($row->total / max(1, $openTickets) * 100)) : 0; @endphp
                            <div class="progress-bar bg-primary" style="width: {{ $pct }}%" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('Workload') }}"></div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-blue-lt">{{ $row->total }}</span>
                    </div>
                </div>
                @empty
                <div class="empty"><p class="empty-title">{{ __('No open assigned tickets.') }}</p></div>
                @endforelse
            </div>
        </div>

        <div class="row row-cards mt-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">{{ __('SLA At Risk') }}</h3></div>
                    <div class="list-group list-group-flush">
                        @forelse($slaAtRisk ?? [] as $ticket)
                        <a href="{{ route('admin.tickets.show', $ticket) }}" class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col text-truncate"><strong>{{ $ticket->uid }}</strong> — {{ $ticket->subject }}</div>
                                <div class="col-auto"><span class="badge bg-yellow-lt">{{ __('at risk') }}</span></div>
                            </div>
                        </a>
                        @empty
                        <div class="list-group-item text-muted">{{ __('No tickets at risk.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">{{ __('SLA Breached') }}</h3></div>
                    <div class="list-group list-group-flush">
                        @forelse($slaBreachedTop ?? [] as $ticket)
                        <a href="{{ route('admin.tickets.show', $ticket) }}" class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col text-truncate"><strong>{{ $ticket->uid }}</strong> — {{ $ticket->subject }}</div>
                                <div class="col-auto"><span class="badge bg-red-lt">{{ __('breached') }}</span></div>
                            </div>
                        </a>
                        @empty
                        <div class="list-group-item text-muted">{{ __('No breached tickets.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">{{ __('Recent Users') }}</h3>
                <div class="card-actions">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm">{{ __('View all') }}</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Joined') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers ?? [] as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm me-2">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    <div>
                                        <div>{{ $user->name }}</div>
                                        <div class="text-muted small">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted">{{ wib($user->created_at, 'd F Y', false) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2"><div class="empty"><p class="empty-title">{{ __('No users yet.') }}</p></div></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    if (typeof window.ApexCharts === 'undefined') return;

    var trendsEl = document.querySelector('#chart-trends');
    if (trendsEl) {
        new window.ApexCharts(trendsEl, {
            chart: { type: 'area', height: 240, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: '{{ __('Tickets') }}', data: @json(($ticketTrends ?? ['values' => []])['values'] ?? []) }],
            xaxis: { categories: @json(($ticketTrends ?? ['labels' => []])['labels'] ?? []) },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            colors: ['#066fd1'],
            grid: { strokeDashArray: 4 },
        }).render();
    }

    var statusEl = document.querySelector('#chart-status');
    if (statusEl) {
        var byStatus = @json($byStatus ?? []);
        new window.ApexCharts(statusEl, {
            chart: { type: 'donut', height: 240, fontFamily: 'inherit' },
            series: Object.values(byStatus),
            labels: Object.keys(byStatus).map(function (s) { return s.replace(/_/g, ' '); }),
            legend: { position: 'bottom' },
            dataLabels: { enabled: true },
        }).render();
    }
})();
</script>

@endsection

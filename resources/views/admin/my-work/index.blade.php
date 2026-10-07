@extends('layouts.admin')
@section('title', __('My Work'))
@section('content')

<div class="row row-cards">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('My Tickets') }} ({{ $assigned->count() }})</h3></div>
            <div class="list-group list-group-flush">
                @forelse($assigned as $ticket)
                <a href="{{ route('admin.tickets.show', $ticket) }}" class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col text-truncate"><strong>{{ $ticket->uid }}</strong> — {{ $ticket->subject }}</div>
                        <div class="col-auto"><span class="badge bg-blue-lt">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span></div>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-muted">{{ __('Nothing assigned. Enjoy the calm.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Unassigned') }} ({{ $unassigned->count() }})</h3></div>
            <div class="list-group list-group-flush">
                @forelse($unassigned as $ticket)
                <a href="{{ route('admin.tickets.show', $ticket) }}" class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col text-truncate"><strong>{{ $ticket->uid }}</strong> — {{ $ticket->subject }}</div>
                        <div class="col-auto text-muted small">{{ $ticket->department->name ?? '' }}</div>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-muted">{{ __('Queue is clear.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('SLA At Risk') }}</h3></div>
            <div class="list-group list-group-flush">
                @forelse($atRisk as $ticket)
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
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('Overdue') }}</h3></div>
            <div class="list-group list-group-flush">
                @forelse($overdue as $ticket)
                <a href="{{ route('admin.tickets.show', $ticket) }}" class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col text-truncate"><strong>{{ $ticket->uid }}</strong> — {{ $ticket->subject }}</div>
                        <div class="col-auto"><span class="badge bg-red-lt">{{ __('breached') }}</span></div>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-muted">{{ __('No overdue tickets.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('High Priority') }}</h3></div>
            <div class="list-group list-group-flush">
                @forelse($highPriority as $ticket)
                <a href="{{ route('admin.tickets.show', $ticket) }}" class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col text-truncate"><strong>{{ $ticket->uid }}</strong> — {{ $ticket->subject }}</div>
                        <div class="col-auto"><span class="badge bg-red-lt">urgent</span></div>
                    </div>
                </a>
                @empty
                <div class="list-group-item text-muted">{{ __('No urgent tickets.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('My Recent Activity') }}</h3></div>
            <div class="list-group list-group-flush">
                @forelse($activity as $log)
                <div class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col text-truncate">{{ $log->action }} — {{ $log->target_label }}</div>
                        <div class="col-auto text-muted small">{{ $log->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div class="list-group-item text-muted">{{ __('No recent activity.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary">Open Tickets</div>
                    <div class="fs-1 fw-bold text-yellow">{{ $stats['open_tickets'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary">Total Tickets</div>
                    <div class="fs-1 fw-bold text-primary">{{ $stats['total_tickets'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary">Resolved</div>
                    <div class="fs-1 fw-bold text-success">{{ $stats['resolved_tickets'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Tickets</h3>
            <div class="card-actions">
                <a href="{{ route('user.tickets.create') }}" class="btn btn-primary btn-sm">New Ticket</a>
            </div>
        </div>
        <div class="card-body">
            @if(isset($recentTickets) && count($recentTickets) > 0)
                @foreach($recentTickets as $ticket)
                    @php
                        $st = (string) ($ticket->status?->value ?? $ticket->status);
                        $statusClass = match($st) {
                            'open', 'resolved', 'answered' => 'bg-green-lt',
                            'pending', 'waiting' => 'bg-yellow-lt',
                            'in_progress' => 'bg-blue-lt',
                            default => 'bg-secondary-lt',
                        };
                    @endphp
                    <a href="{{ route('user.tickets.show', $ticket) }}" class="card card-link mb-2">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span>{{ $ticket->subject }}</span>
                                    <span class="badge {{ $statusClass }} ms-2">
                                        {{ ucfirst(str_replace('_', ' ', $st)) }}
                                    </span>
                                </div>
                                <span class="text-secondary small">{{ $ticket->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            @else
                <div class="empty">
                    <p class="empty-title">No tickets yet</p>
                    <p class="empty-subtitle">Create your first ticket to get started.</p>
                    <div class="empty-action">
                        <a href="{{ route('user.tickets.create') }}" class="btn btn-primary">Create your first ticket</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

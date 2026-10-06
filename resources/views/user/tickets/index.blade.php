<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('My Tickets') }}</h2>
    </x-slot>

    <div class="mb-3">
        <a href="{{ route('user.tickets.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M12 4v16m8-8H4"/></svg>
            New Ticket
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets ?? [] as $ticket)
                    @php
                        $st = (string) ($ticket->status?->value ?? $ticket->status);
                        $pr = strtolower((string) ($ticket->priority ?? 'medium'));
                        $statusClass = match($st) {
                            'open', 'resolved', 'answered' => 'bg-green-lt',
                            'pending', 'waiting' => 'bg-yellow-lt',
                            'in_progress' => 'bg-blue-lt',
                            default => 'bg-secondary-lt',
                        };
                        $priorityClass = match($pr) {
                            'urgent' => 'bg-red-lt',
                            'high' => 'bg-orange-lt',
                            'medium' => 'bg-yellow-lt',
                            default => 'bg-green-lt',
                        };
                    @endphp
                    <tr>
                        <td class="text-secondary">#{{ $ticket->id }}</td>
                        <td>
                            <a href="{{ route('user.tickets.show', $ticket) }}">{{ $ticket->subject }}</a>
                        </td>
                        <td>
                            <span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $priorityClass }}">{{ ucfirst($ticket->priority ?? 'medium') }}</span>
                        </td>
                        <td class="text-secondary">{{ $ticket->updated_at->diffForHumans() }}</td>
                        <td class="text-end">
                            <a href="{{ route('user.tickets.show', $ticket) }}" class="btn btn-sm">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty">
                                <p class="empty-title">No tickets yet</p>
                                <p class="empty-subtitle">Create your first ticket and we will help you out.</p>
                                <div class="empty-action">
                                    <a href="{{ route('user.tickets.create') }}" class="btn btn-primary">Create one</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-center">
            {{ ($tickets ?? collect())->links() }}
        </div>
    </div>
</x-app-layout>

@extends('layouts.admin')
@section('title', 'Tickets')
@section('page-actions')
    <a href="{{ route('admin.export.tickets', request()->only(['search','status','priority','from','to'])) }}" class="btn">
        Export CSV
    </a>
    <a href="{{ route('admin.tickets.create') }}" class="btn btn-primary">
        New Ticket
    </a>
@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form action="{{ route('admin.tickets.index') }}" method="GET">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets..." class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select">
                        <option value="">All Priority</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<form id="bulk-form" action="{{ route('admin.tickets.bulk') }}" method="POST">
    @csrf
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" class="form-check-input"></th>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets ?? [] as $ticket)
                    @php $st = (string) ($ticket->status?->value ?? $ticket->status); @endphp
                    <tr>
                        <td>
                            <input type="checkbox" name="ids[]" value="{{ $ticket->id }}" class="form-check-input">
                        </td>
                        <td class="text-muted">#{{ $ticket->id }}</td>
                        <td>
                            <a href="{{ route('admin.tickets.show', $ticket) }}">{{ $ticket->subject }}</a>
                        </td>
                        <td class="text-muted">{{ $ticket->user->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $st === 'open' ? 'green' : ($st === 'pending' ? 'yellow' : ($st === 'resolved' ? 'green' : 'secondary')) }}-lt">
                                {{ ucfirst($st) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ ($ticket->priority ?? '') === 'urgent' ? 'red' : (($ticket->priority ?? '') === 'high' ? 'orange' : (($ticket->priority ?? '') === 'medium' ? 'yellow' : 'green')) }}-lt">
                                {{ ucfirst($ticket->priority ?? 'normal') }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $ticket->assignedTo->name ?? 'Unassigned' }}</td>
                        <td class="text-muted">{{ wib($ticket->created_at, 'd F Y', false) }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-sm">View</a>
                            <a href="{{ route('admin.tickets.edit', $ticket) }}" class="btn btn-sm">Edit</a>
                            <form action="{{ route('admin.tickets.destroy', $ticket) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this ticket?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty">
                                <p class="empty-title">No tickets found</p>
                                <p class="empty-subtitle text-muted">There are no tickets matching your filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto">
                            <select name="action" class="form-select">
                                <option value="">Bulk Actions</option>
                                <option value="close">Close Selected</option>
                                <option value="delete">Delete Selected</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn">Apply</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card-footer d-flex align-items-center justify-content-center">
                        {{ ($tickets ?? collect())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.getElementById('select-all').addEventListener('change', function() {
        document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = this.checked);
    });
</script>
@endsection

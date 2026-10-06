@extends('layouts.admin')
@section('title', 'Conversations')
@section('page-actions')
    <a href="{{ route('admin.conversations.create') }}" class="btn btn-primary">
        New Conversation
    </a>
@endsection
@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Subject</th>
                    <th>Channel</th>
                    <th>Status</th>
                    <th>Messages</th>
                    <th>Last Message</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($conversations ?? [] as $conversation)
                <tr>
                    <td>
                        <span class="avatar avatar-sm me-2">{{ substr($conversation->user->name ?? 'U', 0, 1) }}</span>
                        <strong>{{ $conversation->user->name ?? 'Guest' }}</strong>
                    </td>
                    <td class="text-muted">{{ Str::limit($conversation->subject ?? 'Chat', 40) }}</td>
                    <td class="text-muted">{{ ucfirst($conversation->channel ?? 'web') }}</td>
                    <td>
                        <span class="badge bg-{{ ($conversation->status ?? '') === 'active' ? 'green' : (($conversation->status ?? '') === 'waiting' ? 'yellow' : 'secondary') }}-lt">
                            {{ ucfirst($conversation->status ?? 'Active') }}
                        </span>
                    </td>
                    <td class="text-muted">{{ $conversation->messages_count ?? 0 }}</td>
                    <td class="text-muted">{{ $conversation->updated_at->diffForHumans() }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.conversations.show', $conversation) }}" class="btn btn-sm">View</a>
                        <form action="{{ route('admin.conversations.destroy', $conversation) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty">
                            <p class="empty-title">No conversations found</p>
                            <p class="empty-subtitle text-muted">Start a new conversation to get going.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-center">
        {{ ($conversations ?? collect())->links() }}
    </div>
</div>
@endsection

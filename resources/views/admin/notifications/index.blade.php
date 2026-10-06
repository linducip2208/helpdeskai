@extends('layouts.admin')
@section('title', 'Notifications')
@section('content')
<div class="mb-3">
    <h2 class="page-title">Notifications</h2>
    <p class="text-muted">Riwayat notifikasi untuk akun {{ auth()->user()->name }}.</p>
</div>

<div class="card">
    <div class="list-group list-group-flush">
        @forelse($notifications as $n)
            @php
                $data = is_array($n->data) ? $n->data : (array) json_decode($n->data ?? '[]', true);
            @endphp
            <div class="list-group-item {{ $n->read_at ? '' : 'bg-primary-lt' }}">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="badge badge-dot {{ $n->read_at ? 'bg-secondary' : 'bg-primary' }}"></span>
                    </div>
                    <div class="col">
                        <h4 class="mb-1">{{ $data['title'] ?? class_basename($n->type) }}</h4>
                        <p class="text-muted mb-1">{{ $data['body'] ?? $data['message'] ?? '' }}</p>
                        @if(! empty($data['url']))
                            <a href="{{ $data['url'] }}" class="small">Open &rarr;</a>
                        @endif
                    </div>
                    <div class="col-auto text-muted small">{{ $n->created_at?->diffForHumans() }}</div>
                    <div class="col-auto">
                        <form method="POST" action="{{ route('admin.notifications.destroy', $n) }}" onsubmit="return confirm('Delete this notification?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card-body">
                <div class="empty">
                    <p class="empty-title">No notifications yet</p>
                    <p class="empty-subtitle text-muted">You have no notifications at this time.</p>
                </div>
            </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
        <div class="card-footer d-flex align-items-center justify-content-center">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection

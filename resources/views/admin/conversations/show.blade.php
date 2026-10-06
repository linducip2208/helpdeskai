@extends('layouts.admin')
@section('title', 'Conversation #' . ($conversation->id ?? '0'))
@section('content')
<div class="mb-3">
    <a href="{{ route('admin.conversations.index') }}">&larr; Back to Conversations</a>
    <h2 class="page-title mt-1">Conversation with {{ $conversation->user->name ?? 'Guest' }}</h2>
</div>

<div class="card">
    <div class="card-body" id="messages-container" style="height: calc(100vh - 350px); min-height: 400px; overflow-y: auto;">
        @forelse($conversation->messages ?? [] as $message)
        @if($message->sender_type === 'agent')
        <div class="card bg-primary-lt mb-2">
            <div class="card-body py-2">
                <p class="mb-1">{{ $message->body }}</p>
                <p class="text-muted small mb-0">{{ $message->created_at->format('H:i') }}</p>
            </div>
        </div>
        @else
        <div class="card mb-2">
            <div class="card-body py-2">
                <p class="mb-1">{{ $message->body }}</p>
                <p class="text-muted small mb-0">{{ $message->created_at->format('H:i') }}</p>
            </div>
        </div>
        @endif
        @empty
        <div class="empty">
            <p class="empty-title">No messages yet</p>
            <p class="empty-subtitle text-muted">Send the first message below.</p>
        </div>
        @endforelse
    </div>
    <div class="card-footer">
        <form action="{{ route('admin.conversations.reply', $conversation ?? 0) }}" method="POST">
            @csrf
            <div class="row g-2">
                <div class="col">
                    <input type="text" name="message" placeholder="Type your message..." class="form-control">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Send</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const container = document.getElementById('messages-container');
    if (container) container.scrollTop = container.scrollHeight;
</script>
@endsection

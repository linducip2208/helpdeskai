<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Ticket #') . ($ticket->id ?? '0') }}</h2>
    </x-slot>

    <div class="mb-3">
        <a href="{{ route('user.tickets.index') }}">&larr; Back to My Tickets</a>
    </div>

    @php $st = (string) ($ticket->status?->value ?? $ticket->status ?? ''); @endphp
    @php
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

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="card-title mb-0">{{ $ticket->subject ?? '' }}</h3>
                <span class="badge {{ $statusClass }}">{{ $st !== '' ? ucfirst(str_replace('_', ' ', $st)) : 'Unknown' }}</span>
            </div>
            <div class="mb-3">
                {!! nl2br(e($ticket->body ?? '')) !!}
            </div>
            @include('tickets._attachments', ['attachments' => ($ticket->attachments ?? collect())->where('is_internal', false), 'downloadRoute' => 'user.attachments.download'])
            @include('tickets._custom_field_values', ['customFieldValues' => $customFieldValues ?? []])
            <div class="d-flex flex-wrap gap-3 text-secondary border-top pt-3">
                <span>Department: <strong>{{ $ticket->department->name ?? 'N/A' }}</strong></span>
                <span>Category: <strong>{{ $ticket->category->name ?? 'N/A' }}</strong></span>
                <span>Priority: <span class="badge {{ $priorityClass }}">{{ ucfirst($ticket->priority ?? 'medium') }}</span></span>
                <span>Created: <strong>{{ wib($ticket->created_at) }}</strong></span>
            </div>
        </div>
    </div>

    <h3 class="card-title mb-2">Replies</h3>
    @forelse($ticket->replies ?? [] as $reply)
    @if(!$reply->is_internal)
    <div class="card mb-2">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center">
                    <span class="avatar avatar-sm me-2">{{ substr($reply->user->name ?? 'U', 0, 1) }}</span>
                    <div>
                        <div>{{ $reply->user->name ?? 'Unknown' }}</div>
                        <div class="text-secondary small">{{ wib($reply->created_at) }}</div>
                    </div>
                </div>
            </div>
            <div>
                {!! nl2br(e($reply->body ?? '')) !!}
            </div>
            @include('tickets._attachments', ['attachments' => ($reply->attachments ?? collect())->where('is_internal', false), 'downloadRoute' => 'user.attachments.download'])
        </div>
    </div>
    @endif
    @empty
    <div class="card">
        <div class="card-body">
            <div class="empty">
                <p class="empty-title">No replies yet</p>
            </div>
        </div>
    </div>
    @endforelse

    @if($st !== 'closed')
    <div class="card mt-3">
        <div class="card-body">
            <h3 class="card-title">Add Reply</h3>
            <form action="{{ route('user.tickets.reply', $ticket ?? 0) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <textarea name="body" rows="4" class="form-control" placeholder="Write your reply..." required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="reply-attachments">Attachments (max 5 files, 10 MB each)</label>
                    <input type="file" id="reply-attachments" name="attachments[]" multiple class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Send Reply</button>
            </form>
        </div>
    </div>
    @endif
</x-app-layout>

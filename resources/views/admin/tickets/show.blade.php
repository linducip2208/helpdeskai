@extends('layouts.admin')
@section('title', 'Ticket #' . ($ticket->id ?? '0'))
@section('page-actions')
    <a href="{{ route('admin.tickets.edit', $ticket ?? 0) }}" class="btn">Edit</a>
    <form action="{{ route('admin.tickets.destroy', $ticket ?? 0) }}" method="POST" class="d-inline">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete?')">Delete</button>
    </form>
@endsection
@section('content')
<div class="mb-3">
    <a href="{{ route('admin.tickets.index') }}">&larr; Back to Tickets</a>
    <h2 class="page-title mt-1">Ticket #{{ $ticket->id ?? '0' }} — {{ $ticket->subject ?? '' }}</h2>
</div>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                {!! nl2br(e($ticket->body ?? '')) !!}
                @include('tickets._attachments', ['attachments' => $ticket->attachments ?? collect(), 'downloadRoute' => 'admin.attachments.download'])
            </div>
        </div>

        <h3 class="card-title mb-2">Replies</h3>
        @forelse($ticket->replies ?? [] as $reply)
        @php $isInternal = (bool) ($reply->is_internal ?? false); @endphp
        @if($isInternal)
        <div class="card bg-yellow-lt mb-2">
            <div class="card-body">
                <div class="row align-items-center mb-2">
                    <div class="col">
                        <span class="avatar avatar-sm me-2">{{ substr($reply->user->name ?? 'U', 0, 1) }}</span>
                        <strong>{{ $reply->user->name ?? 'Unknown' }}</strong>
                        <span class="text-muted small ms-2">{{ wib($reply->created_at) }}</span>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-yellow-lt">INTERNAL</span>
                        @if($reply->sentiment)
                            @php
                                $sClass = match($reply->sentiment) {
                                    'positive' => 'green',
                                    'neutral'  => 'secondary',
                                    'negative' => 'yellow',
                                    'angry'    => 'red',
                                    default    => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $sClass }}-lt" title="AI sentiment">
                                {{ ucfirst($reply->sentiment) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div>
                    {!! nl2br(e($reply->body ?? '')) !!}
                </div>
                @include('tickets._attachments', ['attachments' => $reply->attachments ?? collect(), 'downloadRoute' => 'admin.attachments.download'])
            </div>
        </div>
        @elseif($reply->user_id === ($ticket->user_id ?? 0))
        <div class="card mb-2">
            <div class="card-body">
                <div class="row align-items-center mb-2">
                    <div class="col">
                        <span class="avatar avatar-sm me-2">{{ substr($reply->user->name ?? 'U', 0, 1) }}</span>
                        <strong>{{ $reply->user->name ?? 'Unknown' }}</strong>
                        <span class="text-muted small ms-2">{{ wib($reply->created_at) }}</span>
                    </div>
                    <div class="col-auto">
                        @if($reply->sentiment)
                            @php
                                $sClass = match($reply->sentiment) {
                                    'positive' => 'green',
                                    'neutral'  => 'secondary',
                                    'negative' => 'yellow',
                                    'angry'    => 'red',
                                    default    => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $sClass }}-lt" title="AI sentiment">
                                {{ ucfirst($reply->sentiment) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div>
                    {!! nl2br(e($reply->body ?? '')) !!}
                </div>
                @include('tickets._attachments', ['attachments' => $reply->attachments ?? collect(), 'downloadRoute' => 'admin.attachments.download'])
            </div>
        </div>
        @else
        <div class="card bg-primary-lt mb-2">
            <div class="card-body">
                <div class="row align-items-center mb-2">
                    <div class="col">
                        <span class="avatar avatar-sm me-2">{{ substr($reply->user->name ?? 'U', 0, 1) }}</span>
                        <strong>{{ $reply->user->name ?? 'Unknown' }}</strong>
                        <span class="text-muted small ms-2">{{ wib($reply->created_at) }}</span>
                    </div>
                    <div class="col-auto">
                        @if($reply->sentiment)
                            @php
                                $sClass = match($reply->sentiment) {
                                    'positive' => 'green',
                                    'neutral'  => 'secondary',
                                    'negative' => 'yellow',
                                    'angry'    => 'red',
                                    default    => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $sClass }}-lt" title="AI sentiment">
                                {{ ucfirst($reply->sentiment) }}
                            </span>
                        @endif
                    </div>
                </div>
                <div>
                    {!! nl2br(e($reply->body ?? '')) !!}
                </div>
                @include('tickets._attachments', ['attachments' => $reply->attachments ?? collect(), 'downloadRoute' => 'admin.attachments.download'])
            </div>
        </div>
        @endif
        @empty
        <div class="card mb-3">
            <div class="card-body">
                <div class="empty">
                    <p class="empty-title">No replies yet</p>
                    <p class="empty-subtitle text-muted">Be the first to reply to this ticket.</p>
                </div>
            </div>
        </div>
        @endforelse

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Add Reply</h3>
                <div class="card-actions">
                    <button type="button" id="ai-suggest-btn" class="btn btn-sm">
                        <span id="ai-suggest-label">Suggest reply with AI</span>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.tickets.reply', $ticket ?? 0) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <textarea id="reply-body" name="body" rows="4" class="form-control" placeholder="Write your reply..."></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="reply-attachments">Attachments (max 5 files, 10 MB each)</label>
                        <input type="file" id="reply-attachments" name="attachments[]" multiple class="form-control">
                    </div>
                    <p id="ai-suggest-error" class="text-danger small mb-2 d-none"></p>
                    <div class="row align-items-center">
                        <div class="col">
                            <label class="form-check">
                                <input type="checkbox" name="is_internal" value="1" class="form-check-input">
                                <span class="form-check-label">Internal note (hidden from customer)</span>
                            </label>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Send Reply</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
        (function() {
            const btn = document.getElementById('ai-suggest-btn');
            const label = document.getElementById('ai-suggest-label');
            const body = document.getElementById('reply-body');
            const err = document.getElementById('ai-suggest-error');
            if (!btn) return;
            btn.addEventListener('click', async () => {
                btn.disabled = true;
                err.classList.add('d-none');
                label.textContent = 'Thinking…';
                try {
                    const res = await fetch('{{ route('admin.tickets.suggest', $ticket) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();
                    if (res.ok && data.suggestion) {
                        body.value = data.suggestion;
                        body.focus();
                    } else {
                        err.textContent = data.error || 'AI request failed.';
                        err.classList.remove('d-none');
                    }
                } catch (e) {
                    err.textContent = 'Network error: ' + e.message;
                    err.classList.remove('d-none');
                } finally {
                    btn.disabled = false;
                    label.textContent = 'Suggest reply with AI';
                }
            });
        })();
        </script>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <form action="{{ route('admin.tickets.update-status', $ticket ?? 0) }}" method="POST">
                        @csrf @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="form-select">
                            <option value="open" {{ ($ticket->status ?? '') === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="pending" {{ ($ticket->status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="resolved" {{ ($ticket->status ?? '') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ ($ticket->status ?? '') === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </form>
                </div>
                <div class="mb-3">
                    <label class="form-label">Priority</label>
                    <form action="{{ route('admin.tickets.update-priority', $ticket ?? 0) }}" method="POST">
                        @csrf @method('PATCH')
                        <select name="priority" onchange="this.form.submit()" class="form-select">
                            <option value="low" {{ ($ticket->priority ?? '') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ ($ticket->priority ?? '') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ ($ticket->priority ?? '') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ ($ticket->priority ?? '') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </form>
                </div>
                <div>
                    <label class="form-label">Assignment</label>
                    <form action="{{ route('admin.tickets.assign', $ticket ?? 0) }}" method="POST">
                        @csrf @method('PATCH')
                        <select name="assigned_to" onchange="this.form.submit()" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($agents ?? [] as $agent)
                            <option value="{{ $agent->id }}" {{ ($ticket->assigned_to ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>

        @if($ticket->ai_classified_at || $ticket->ai_sentiment)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">AI Insights</h3>
            </div>
            <div class="card-body">
                @if($ticket->ai_classification)
                    @if(! empty($ticket->ai_classification['summary']))
                        <div class="mb-2">
                            <p class="form-label">Summary</p>
                            <p>{{ $ticket->ai_classification['summary'] }}</p>
                        </div>
                    @endif
                    <div class="mb-2">
                        @if(! empty($ticket->ai_classification['department']))
                            <span class="badge bg-secondary-lt">Dept: {{ $ticket->ai_classification['department'] }}</span>
                        @endif
                        @if(! empty($ticket->ai_classification['category']))
                            <span class="badge bg-secondary-lt">Cat: {{ $ticket->ai_classification['category'] }}</span>
                        @endif
                        @if(! empty($ticket->ai_classification['priority']))
                            <span class="badge bg-secondary-lt">Pri: {{ ucfirst($ticket->ai_classification['priority']) }}</span>
                        @endif
                    </div>
                @endif
                @if($ticket->ai_sentiment)
                    @php
                        $sentColor = match($ticket->ai_sentiment) {
                            'positive' => 'green',
                            'neutral'  => 'secondary',
                            'negative' => 'yellow',
                            'angry'    => 'red',
                            default    => 'secondary',
                        };
                    @endphp
                    <div class="mb-2">
                        <p class="form-label">Latest Sentiment</p>
                        <span class="badge bg-{{ $sentColor }}-lt">{{ ucfirst($ticket->ai_sentiment) }}</span>
                    </div>
                @endif
                @if($ticket->ai_classified_at)
                    <p class="text-muted small">Classified {{ $ticket->ai_classified_at->diffForHumans() }}</p>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="mb-2"><span class="text-muted">Department:</span> <strong class="ms-1">{{ $ticket->department->name ?? 'N/A' }}</strong></div>
                <div class="mb-2"><span class="text-muted">Category:</span> <strong class="ms-1">{{ $ticket->category->name ?? 'N/A' }}</strong></div>
                <div class="mb-2"><span class="text-muted">Created by:</span> <strong class="ms-1">{{ $ticket->user->name ?? 'N/A' }}</strong></div>
                <div class="mb-2"><span class="text-muted">Created:</span> <strong class="ms-1">{{ wib($ticket->created_at) }}</strong></div>
                <div><span class="text-muted">Updated:</span> <strong class="ms-1">{{ $ticket->updated_at->diffForHumans() }}</strong></div>
            </div>
        </div>
    </div>
</div>
@endsection

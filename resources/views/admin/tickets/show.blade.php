@extends('layouts.admin')
@section('title', 'Ticket #' . ($ticket->id ?? '0'))
@section('page-actions')
    @can('tickets.merge')
    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#merge-modal">Merge</button>
    @endcan
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
                @include('tickets._custom_field_values', ['customFieldValues' => $customFieldValues ?? []])
                @include('tickets._attachments', ['attachments' => $ticket->attachments ?? collect(), 'downloadRoute' => 'admin.attachments.download'])
            </div>
        </div>

        <h3 class="card-title mb-2">Replies</h3>
        <div id="ticket-presence" class="d-none align-items-center gap-2 mb-2">
            <div id="presence-avatars" class="avatar-list avatar-list-stacked"></div>
            <span id="presence-text" class="text-muted small"></span>
        </div>
        <script>
        (function () {
            if (!window.Echo) return;
            var wrap = document.getElementById('ticket-presence');
            var avatars = document.getElementById('presence-avatars');
            var text = document.getElementById('presence-text');
            var me = {{ auth()->id() }};
            function render(members) {
                var others = members.filter(function (m) { return m.id !== me; });
                if (!others.length) { wrap.classList.add('d-none'); wrap.classList.remove('d-flex'); return; }
                wrap.classList.remove('d-none'); wrap.classList.add('d-flex');
                avatars.innerHTML = others.slice(0, 5).map(function (m) {
                    return '<span class="avatar avatar-xs" title="' + m.name.replace(/"/g, '') + '">' + m.name.charAt(0).toUpperCase() + '</span>';
                }).join('');
                text.textContent = others.length === 1
                    ? others[0].name + ' is also viewing this ticket'
                    : others.length + ' people are also viewing this ticket';
            }
            try {
                var channel = window.Echo.join('ticket.{{ $ticket->id ?? 0 }}');
                channel.here(render);
                channel.joining(function () { channel.here(render); });
                channel.leaving(function () { channel.here(render); });
            } catch (e) {}
        })();
        </script>
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

        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-2"><span class="text-muted">Department:</span> <strong class="ms-1">{{ $ticket->department->name ?? 'N/A' }}</strong></div>
                <div class="mb-2"><span class="text-muted">Category:</span> <strong class="ms-1">{{ $ticket->category->name ?? 'N/A' }}</strong></div>
                <div class="mb-2"><span class="text-muted">Created by:</span> <strong class="ms-1">{{ $ticket->user->name ?? 'N/A' }}</strong></div>
                <div class="mb-2"><span class="text-muted">Created:</span> <strong class="ms-1">{{ wib($ticket->created_at) }}</strong></div>
                <div><span class="text-muted">Updated:</span> <strong class="ms-1">{{ $ticket->updated_at->diffForHumans() }}</strong></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Time Tracking</h3>
                <div class="card-actions"><span class="badge bg-blue-lt">{{ intdiv($timeTotal ?? 0, 60) }}h {{ ($timeTotal ?? 0) % 60 }}m</span></div>
            </div>
            <div class="card-body">
                @forelse($timeEntries ?? [] as $entry)
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        <div>{{ $entry->note ?? '—' }}</div>
                        <div class="text-muted small">{{ $entry->user->name ?? '—' }} · {{ wib($entry->worked_at, 'd F Y', false) }}</div>
                    </div>
                    <span class="badge bg-secondary-lt">{{ $entry->minutes }}m</span>
                </div>
                @empty
                <p class="text-muted small mb-3">No time logged yet.</p>
                @endforelse
                <form action="{{ route('admin.tickets.log-time', $ticket ?? 0) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-4">
                            <input type="number" name="minutes" min="1" max="1440" placeholder="Min" class="form-control" required>
                        </div>
                        <div class="col-8">
                            <input type="text" name="note" maxlength="255" placeholder="Note (optional)" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm mt-2">Log Time</button>
                </form>
            </div>
        </div>
    </div>
</div>

@can('tickets.merge')
<div class="modal modal-blur fade" id="merge-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Merge Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.tickets.merge', $ticket ?? 0) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Move all replies, attachments and time entries from another ticket into this one. The other ticket will be closed.</p>
                    <label class="form-label" for="target_uid">Source ticket UID (e.g. TKT-ABCDE)</label>
                    <input type="text" name="target_uid" id="target_uid" class="form-control font-monospace" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Merge</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@extends('layouts.admin')
@section('title', 'Ticket #' . ($ticket->id ?? '0'))
@section('page-actions')
<div class="modal modal-blur fade" id="split-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Split into follow-up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.tickets.split', $ticket ?? 0) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="split-subject">Subject</label>
                        <input type="text" name="subject" id="split-subject" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="split-body">Description</label>
                        <textarea name="body" id="split-body" rows="4" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Split</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey || e.metaKey || e.altKey) return;
        if (e.target.matches('input, textarea, select')) return;
        var body = document.getElementById('reply-body');
        if (e.key === 'r' && body) { body.focus(); }
        if (e.key === '?') { alert('Shortcuts: R = reply, Ctrl+K = command palette'); }
    });
})();
</script>

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
            window.helpdeskToast = function (msg) { toast(msg); };
            function toast(msg) {
                var area = document.getElementById('ticket-toasts');
                if (!area) return;
                var el = document.createElement('div');
                el.className = 'alert alert-info alert-dismissible';
                el.setAttribute('role', 'status');
                el.innerHTML = '<div></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                el.querySelector('div').textContent = msg + ' ';
                var link = document.createElement('a');
                link.href = '';
                link.className = 'alert-link';
                link.textContent = 'Refresh';
                el.querySelector('div').appendChild(link);
                area.appendChild(el);
                setTimeout(function () { el.remove(); }, 15000);
            }
        })();
        </script>
        <div id="ticket-toasts" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080; max-width: 22rem;" aria-live="polite"></div>
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
                channel.listen('.ticket.replied', function () { window.helpdeskToast('New reply on this ticket.'); });
                channel.listen('.ticket.status', function (e) { window.helpdeskToast('Status changed to ' + (e.to || '') + '.'); });
                channel.listen('.ticket.assigned', function () { window.helpdeskToast('Ticket reassigned.'); });
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
                <div class="card-actions d-flex gap-1">
                    <select id="canned-select" class="form-select form-select-sm" style="max-width: 12rem;" aria-label="Insert canned response">
                        <option value="">Canned…</option>
                        @foreach($cannedResponses ?? [] as $canned)
                        <option value="{{ $canned->id }}">{{ $canned->title }}</option>
                        @endforeach
                    </select>
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
            const canned = document.getElementById('canned-select');
            const body = document.getElementById('reply-body');
            if (canned && body) {
                canned.addEventListener('change', async () => {
                    if (!canned.value) return;
                    try {
                        const res = await fetch('/admin/canned-responses/' + canned.value + '/use', { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        if (res.ok && data.data && data.data.body) {
                            body.value = (body.value ? body.value + "\n\n" : '') + data.data.body;
                            body.focus();
                        }
                    } catch (e) {}
                    canned.value = '';
                });
            }
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
                <div class="d-flex flex-wrap gap-1 mt-2">
                    <button type="button" class="btn btn-sm" data-ai-action="summary">Summarize</button>
                    <button type="button" class="btn btn-sm" data-ai-action="similar">Similar tickets</button>
                    <button type="button" class="btn btn-sm" data-ai-action="recommend">KB articles</button>
                    <button type="button" class="btn btn-sm" data-ai-action="answer">KB answer</button>
                </div>
                <div id="ai-assistant-result" class="mt-2 d-none">
                    <p class="form-label mb-1">AI generated · <span id="ai-assistant-time"></span></p>
                    <div id="ai-assistant-body" class="text-muted small"></div>
                </div>
            </div>
        </div>
        <script>
        (function () {
            var box = document.getElementById('ai-assistant-result');
            var body = document.getElementById('ai-assistant-body');
            var time = document.getElementById('ai-assistant-time');
            if (!box) return;
            var routes = {
                summary: { url: '{{ route('admin.tickets.ai-summary', $ticket) }}', method: 'POST' },
                similar: { url: '{{ route('admin.tickets.ai-similar', $ticket) }}', method: 'GET' },
                recommend: { url: '{{ route('admin.tickets.ai-recommend', $ticket) }}', method: 'GET' },
                answer: { url: '{{ route('admin.tickets.ai-answer', $ticket) }}', method: 'POST' },
            };
            function esc(s) { return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;'); }
            document.querySelectorAll('[data-ai-action]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var r = routes[btn.getAttribute('data-ai-action')];
                    if (!r) return;
                    btn.disabled = true;
                    body.textContent = 'Working…';
                    box.classList.remove('d-none');
                    fetch(r.url, {
                        method: r.method,
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    }).then(function (res) { return res.json().then(function (j) { return { ok: res.ok, j: j }; }); })
                    .then(function (out) {
                        time.textContent = new Date().toLocaleString();
                        if (!out.ok) { body.textContent = (out.j && out.j.message) || 'AI unavailable.'; return; }
                        var d = out.j.data || {};
                        if (Array.isArray(d)) {
                            body.innerHTML = d.length
                                ? '<ul class="mb-0">' + d.map(function (it) { return '<li>' + esc(it.subject || it.title) + (it.uid ? ' (' + esc(it.uid) + ')' : '') + '</li>'; }).join('') + '</ul>'
                                : 'No matches found.';
                        } else if (d.answer) {
                            var src = (d.sources || []).map(function (s) { return esc(s.title); }).join(', ');
                            body.innerHTML = '<p>' + esc(d.answer) + '</p>' + (src ? '<p class="mb-0">Sources: ' + src + (d.confidence != null ? ' · confidence ' + d.confidence : '') + '</p>' : '');
                        } else {
                            body.textContent = d.raw || JSON.stringify(d);
                        }
                    }).catch(function (e) {
                        time.textContent = new Date().toLocaleString();
                        body.textContent = 'Network error: ' + e.message;
                    }).finally(function () { btn.disabled = false; });
                });
            });
        })();
        </script>
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

        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">Tags</h3></div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-1 mb-2">
                    @forelse($ticket->tags ?? [] as $tag)
                    <span class="badge bg-{{ $tag->color }}-lt">{{ $tag->name }}</span>
                    @empty
                    <span class="text-muted small">No tags.</span>
                    @endforelse
                </div>
                <form action="{{ route('admin.tickets.tags.store', $ticket ?? 0) }}" method="POST">
                    @csrf
                    <div class="input-group input-group-sm">
                        <input type="text" name="name" maxlength="100" placeholder="Add tag…" class="form-control" required>
                        <button type="submit" class="btn">Add</button>
                    </div>
                </form>
                <form action="{{ route('admin.tickets.update', $ticket ?? 0) }}" method="POST" class="d-none"></form>
                <div class="d-flex gap-1">
                    @if($watching ?? false)
                    <form action="{{ route('admin.tickets.unwatch', $ticket ?? 0) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm">Unwatch</button>
                    </form>
                    @else
                    <form action="{{ route('admin.tickets.watch', $ticket ?? 0) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm">Watch</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">Related Tickets</h3></div>
            <div class="card-body">
                @forelse($ticket->links ?? [] as $link)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="badge bg-secondary-lt">{{ $link->relation }}</span>
                        <a href="{{ route('admin.tickets.show', $link->linked_ticket_id) }}">{{ $link->linkedTicket->uid ?? '#'.$link->linked_ticket_id }}</a>
                    </div>
                    <form action="{{ route('admin.tickets.unlink', [$ticket ?? 0, $link]) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-link text-danger" onclick="return confirm('Remove link?')">×</button>
                    </form>
                </div>
                @empty
                <p class="text-muted small mb-2">No linked tickets.</p>
                @endforelse
                <form action="{{ route('admin.tickets.link', $ticket ?? 0) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-5">
                            <input type="text" name="target_uid" class="form-control form-control-sm" placeholder="TKT-XXXXX" required>
                        </div>
                        <div class="col-4">
                            <select name="relation" class="form-select form-select-sm">
                                @foreach(['related', 'parent', 'child', 'duplicate', 'blocked_by', 'follow_up'] as $rel)
                                <option value="{{ $rel }}">{{ $rel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <button type="submit" class="btn btn-sm w-100">Link</button>
                        </div>
                    </div>
                </form>
                <button type="button" class="btn btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#split-modal">Split into follow-up</button>
            </div>
        </div>

        @if(! empty($similar))
        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">Possible Duplicates</h3></div>
            <div class="list-group list-group-flush">
                @foreach($similar as $s)
                <a href="{{ route('admin.tickets.show', $s['id']) }}" class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col text-truncate"><strong>{{ $s['uid'] }}</strong> — {{ $s['subject'] }}</div>
                        <div class="col-auto"><span class="badge bg-yellow-lt">{{ $s['score'] }}</span></div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        @if(! empty($macros))
        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">Macros</h3></div>
            <div class="card-body">
                @foreach($macros as $macro)
                <form action="{{ route('admin.macros.apply', [$macro, $ticket ?? 0]) }}" method="POST" class="d-inline" onsubmit="return confirm('Apply macro {{ $macro->name }}?')">
                    @csrf
                    <button type="submit" class="btn btn-sm mb-1">{{ $macro->name }}</button>
                </form>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@can('tickets.merge')
<div class="modal modal-blur fade" id="merge-modal" tabindex="-1" role="dialog" aria-hidden="true">    <div class="modal-dialog modal-dialog-centered" role="document">
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

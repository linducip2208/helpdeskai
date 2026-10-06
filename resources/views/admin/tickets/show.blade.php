@extends('layouts.admin')
@section('title', 'Ticket #' . ($ticket->id ?? '0'))
@section('content')

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('admin.tickets.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 mb-1 inline-block">&larr; Back to Tickets</a>
            <h2 class="text-2xl font-bold text-slate-900">Ticket #{{ $ticket->id ?? '0' }} — {{ $ticket->subject ?? '' }}</h2>
        </div>
        <div class="flex items-center flex-wrap gap-3">
            <a href="{{ route('admin.tickets.edit', $ticket ?? 0) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-lg transition">Edit</a>
            <form action="{{ route('admin.tickets.destroy', $ticket ?? 0) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium rounded-lg transition" onclick="return confirm('Delete?')">Delete</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Ticket Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="prose prose-sm max-w-none text-slate-700">
                    {!! nl2br(e($ticket->body ?? '')) !!}
                </div>
                @if(($ticket->attachments ?? collect())->count() > 0)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-sm font-medium text-slate-500 mb-2">Attachments</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($ticket->attachments as $attachment)
                        <a href="#" class="inline-flex items-center px-3 py-1.5 bg-gray-50 rounded-lg text-sm text-indigo-600 hover:bg-indigo-50 transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            {{ $attachment->name }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Replies -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-slate-900">Replies</h3>
                @forelse($ticket->replies ?? [] as $reply)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 {{ $reply->user_id === ($ticket->user_id ?? 0) ? 'border-l-4 border-l-indigo-500' : 'border-l-4 border-l-emerald-500' }}">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-semibold {{ $reply->user_id === ($ticket->user_id ?? 0) ? 'bg-indigo-500' : 'bg-emerald-500' }} mr-3">
                                {{ substr($reply->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $reply->user->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-slate-400">{{ $reply->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        @if($reply->sentiment)
                            @php
                                $sClass = match($reply->sentiment) {
                                    'positive' => 'bg-emerald-100 text-emerald-700',
                                    'neutral'  => 'bg-slate-100 text-slate-700',
                                    'negative' => 'bg-amber-100 text-amber-700',
                                    'angry'    => 'bg-red-100 text-red-700',
                                    default    => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sClass }}" title="AI sentiment">
                                {{ ucfirst($reply->sentiment) }}
                            </span>
                        @endif
                    </div>
                    <div class="prose prose-sm max-w-none text-slate-700">
                        {!! nl2br(e($reply->body ?? '')) !!}
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-slate-400">
                    No replies yet.
                </div>
                @endforelse
            </div>

            <!-- Reply Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Add Reply</h3>
                    <button type="button" id="ai-suggest-btn" class="inline-flex items-center px-3 py-1.5 bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-medium rounded-lg transition disabled:opacity-50">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span id="ai-suggest-label">Suggest reply with AI</span>
                    </button>
                </div>
                <form action="{{ route('admin.tickets.reply', $ticket ?? 0) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <textarea id="reply-body" name="body" rows="4" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Write your reply..."></textarea>
                    </div>
                    <p id="ai-suggest-error" class="hidden mb-3 text-xs text-red-500"></p>
                    <div class="flex justify-between items-center">
                        <label class="inline-flex items-center text-sm text-slate-500">
                            <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2">
                            Internal note (hidden from customer)
                        </label>
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Send Reply</button>
                    </div>
                </form>
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
                    err.classList.add('hidden');
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
                            err.classList.remove('hidden');
                        }
                    } catch (e) {
                        err.textContent = 'Network error: ' + e.message;
                        err.classList.remove('hidden');
                    } finally {
                        btn.disabled = false;
                        label.textContent = 'Suggest reply with AI';
                    }
                });
            })();
            </script>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
            <!-- Status & Controls -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500 uppercase mb-1">Status</label>
                    <form action="{{ route('admin.tickets.update-status', $ticket ?? 0) }}" method="POST">
                        @csrf @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="open" {{ ($ticket->status ?? '') === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="pending" {{ ($ticket->status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="resolved" {{ ($ticket->status ?? '') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ ($ticket->status ?? '') === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </form>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 uppercase mb-1">Priority</label>
                    <form action="{{ route('admin.tickets.update-priority', $ticket ?? 0) }}" method="POST">
                        @csrf @method('PATCH')
                        <select name="priority" onchange="this.form.submit()" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="low" {{ ($ticket->priority ?? '') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ ($ticket->priority ?? '') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ ($ticket->priority ?? '') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ ($ticket->priority ?? '') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </form>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 uppercase mb-1">Assignment</label>
                    <form action="{{ route('admin.tickets.assign', $ticket ?? 0) }}" method="POST">
                        @csrf @method('PATCH')
                        <select name="assigned_to" onchange="this.form.submit()" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Unassigned</option>
                            @foreach($agents ?? [] as $agent)
                            <option value="{{ $agent->id }}" {{ ($ticket->assigned_to ?? '') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            @if($ticket->ai_classified_at || $ticket->ai_sentiment)
            <div class="bg-gradient-to-br from-violet-50 to-indigo-50 rounded-xl shadow-sm border border-violet-100 p-6 space-y-3">
                <h3 class="text-xs font-semibold text-violet-700 uppercase tracking-wider flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    AI Insights
                </h3>
                @if($ticket->ai_classification)
                    @if(! empty($ticket->ai_classification['summary']))
                        <div>
                            <p class="text-xs text-slate-500 uppercase">Summary</p>
                            <p class="text-sm text-slate-700 mt-0.5">{{ $ticket->ai_classification['summary'] }}</p>
                        </div>
                    @endif
                    <div class="flex flex-wrap gap-2 text-xs">
                        @if(! empty($ticket->ai_classification['department']))
                            <span class="px-2 py-0.5 rounded bg-white text-slate-700 border border-slate-200">Dept: {{ $ticket->ai_classification['department'] }}</span>
                        @endif
                        @if(! empty($ticket->ai_classification['category']))
                            <span class="px-2 py-0.5 rounded bg-white text-slate-700 border border-slate-200">Cat: {{ $ticket->ai_classification['category'] }}</span>
                        @endif
                        @if(! empty($ticket->ai_classification['priority']))
                            <span class="px-2 py-0.5 rounded bg-white text-slate-700 border border-slate-200">Pri: {{ ucfirst($ticket->ai_classification['priority']) }}</span>
                        @endif
                    </div>
                @endif
                @if($ticket->ai_sentiment)
                    @php
                        $sentColor = match($ticket->ai_sentiment) {
                            'positive' => 'bg-emerald-100 text-emerald-700',
                            'neutral'  => 'bg-slate-100 text-slate-700',
                            'negative' => 'bg-amber-100 text-amber-700',
                            'angry'    => 'bg-red-100 text-red-700',
                            default    => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <div>
                        <p class="text-xs text-slate-500 uppercase">Latest Sentiment</p>
                        <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $sentColor }}">{{ ucfirst($ticket->ai_sentiment) }}</span>
                    </div>
                @endif
                @if($ticket->ai_classified_at)
                    <p class="text-[10px] text-slate-400 pt-1 border-t border-violet-100">Classified {{ $ticket->ai_classified_at->diffForHumans() }}</p>
                @endif
            </div>
            @endif

            <!-- Ticket Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3 text-sm">
                <div>
                    <span class="text-slate-400">Department:</span>
                    <span class="text-slate-700 font-medium ml-1">{{ $ticket->department->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-400">Category:</span>
                    <span class="text-slate-700 font-medium ml-1">{{ $ticket->category->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-400">Created by:</span>
                    <span class="text-slate-700 font-medium ml-1">{{ $ticket->user->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-400">Created:</span>
                    <span class="text-slate-700 font-medium ml-1">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div>
                    <span class="text-slate-400">Updated:</span>
                    <span class="text-slate-700 font-medium ml-1">{{ $ticket->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Ticket #') . ($ticket->id ?? '0') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <a href="{{ route('user.tickets.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to My Tickets</a>

            <!-- Ticket Detail -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $ticket->subject ?? '' }}</h3>
                        @php $st = (string) ($ticket->status?->value ?? $ticket->status ?? ''); @endphp
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium
                            {{ $st === 'open' ? 'bg-emerald-100 text-emerald-700' : '' }}
                            {{ in_array($st, ['pending', 'answered', 'in_progress', 'waiting']) ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $st === 'resolved' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $st === 'closed' ? 'bg-gray-100 text-gray-700' : '' }}">
                            {{ $st !== '' ? ucfirst(str_replace('_', ' ', $st)) : 'Unknown' }}
                        </span>
                    </div>
                    <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300 mb-4">
                        {!! nl2br(e($ticket->body ?? '')) !!}
                    </div>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <span>Department: <strong>{{ $ticket->department->name ?? 'N/A' }}</strong></span>
                        <span>Category: <strong>{{ $ticket->category->name ?? 'N/A' }}</strong></span>
                        <span>Priority: <strong>{{ ucfirst($ticket->priority ?? 'normal') }}</strong></span>
                        <span>Created: <strong>{{ $ticket->created_at->format('M d, Y H:i') }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Replies -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Replies</h3>
                @forelse($ticket->replies ?? [] as $reply)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 {{ ($reply->user_id ?? 0) === auth()->id() ? 'border-l-4 border-l-indigo-500' : 'border-l-4 border-l-emerald-500' }}">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-semibold {{ ($reply->user_id ?? 0) === auth()->id() ? 'bg-indigo-500' : 'bg-emerald-500' }} mr-3">
                                {{ substr($reply->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $reply->user->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-400">{{ $reply->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300">
                        {!! nl2br(e($reply->body ?? '')) !!}
                    </div>
                </div>
                @empty
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-12 text-center text-gray-400 dark:text-gray-500">No replies yet.</div>
                @endforelse
            </div>

            <!-- Reply Form -->
            @if($st !== 'closed')
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Add Reply</h3>
                <form action="{{ route('user.tickets.reply', $ticket ?? 0) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <textarea name="body" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Write your reply..." required></textarea>
                    </div>
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Send Reply</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

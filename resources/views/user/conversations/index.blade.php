<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Conversations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($conversations ?? [] as $conversation)
                    <a href="{{ route('user.conversations.show', $conversation) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900 rounded-full flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-semibold text-sm">
                                {{ substr($conversation->agent->name ?? 'S', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $conversation->agent->name ?? 'Support Agent' }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ Str::limit($conversation->last_message ?? 'No messages', 50) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ ($conversation->status ?? '') === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($conversation->status ?? 'Active') }}
                            </span>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $conversation->updated_at->diffForHumans() }}</p>
                        </div>
                    </a>
                    @empty
                    <div class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">No conversations yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Live Chat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg flex flex-col" style="height: calc(100vh - 250px); min-height: 500px;">
                <div class="flex-1 overflow-y-auto p-6 space-y-4" id="chat-container">
                    @forelse($conversation->messages ?? [] as $message)
                    <div class="flex {{ ($message->sender_type ?? '') === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[70%] rounded-2xl px-4 py-3 {{ ($message->sender_type ?? '') === 'user' ? 'bg-indigo-600 text-white rounded-br-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-bl-md' }}">
                            <p class="text-sm">{{ $message->body }}</p>
                            <p class="text-xs mt-1 {{ ($message->sender_type ?? '') === 'user' ? 'text-indigo-200' : 'text-gray-400' }}">
                                {{ $message->created_at->format('H:i') }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="flex items-center justify-center h-full text-gray-400 dark:text-gray-500">Start a conversation!</div>
                    @endforelse
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900">
                    <form action="{{ route('user.conversations.reply', $conversation ?? 0) }}" method="POST" class="flex gap-3">
                        @csrf
                        <input type="text" name="message" placeholder="Type your message..."
                               class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Send</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const container = document.getElementById('chat-container');
        if (container) container.scrollTop = container.scrollHeight;
    </script>
</x-app-layout>

@extends('layouts.admin')
@section('title', 'Conversation #' . ($conversation->id ?? '0'))
@section('content')

<div class="space-y-6">
    <div>
        <a href="{{ route('admin.conversations.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to Conversations</a>
        <h2 class="text-2xl font-bold text-slate-900 mt-1">Conversation with {{ $conversation->user->name ?? 'Guest' }}</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col" style="height: calc(100vh - 250px); min-height: 500px;">
        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4" id="messages-container">
            @forelse($conversation->messages ?? [] as $message)
            <div class="flex {{ $message->sender_type === 'agent' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%] rounded-2xl px-4 py-3 {{ $message->sender_type === 'agent' ? 'bg-indigo-600 text-white rounded-br-md' : 'bg-gray-100 text-slate-700 rounded-bl-md' }}">
                    <p class="text-sm">{{ $message->body }}</p>
                    <p class="text-xs mt-1 {{ $message->sender_type === 'agent' ? 'text-indigo-200' : 'text-slate-400' }}">
                        {{ $message->created_at->format('H:i') }}
                    </p>
                </div>
            </div>
            @empty
            <div class="flex items-center justify-center h-full text-slate-400">No messages yet.</div>
            @endforelse
        </div>

        <!-- Reply Input -->
        <div class="border-t border-gray-200 p-4 bg-gray-50">
            <form action="{{ route('admin.conversations.reply', $conversation ?? 0) }}" method="POST" class="flex gap-3">
                @csrf
                <input type="text" name="message" placeholder="Type your message..."
                       class="flex-1 rounded-lg border-gray-200 text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Send</button>
            </form>
        </div>
    </div>
</div>

<script>
    const container = document.getElementById('messages-container');
    if (container) container.scrollTop = container.scrollHeight;
</script>

@endsection

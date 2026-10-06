<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Live Chat') }}</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card d-flex flex-column" style="height: calc(100vh - 250px); min-height: 500px;">
                <div class="card-body overflow-auto" id="chat-container">
                    @forelse($conversation->messages ?? [] as $message)
                    <div class="d-flex {{ ($message->sender_type ?? '') === 'user' ? 'justify-content-end' : 'justify-content-start' }} mb-2">
                        <div class="rounded-3 px-3 py-2 {{ ($message->sender_type ?? '') === 'user' ? 'bg-primary text-white' : 'bg-secondary-lt' }}" style="max-width: 70%;">
                            <div class="small">{{ $message->body }}</div>
                            <div class="small mt-1 {{ ($message->sender_type ?? '') === 'user' ? 'text-white-50' : 'text-secondary' }}">
                                {{ $message->created_at->format('H:i') }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="empty">
                        <p class="empty-title">Start a conversation!</p>
                    </div>
                    @endforelse
                </div>

                <div class="card-footer">
                    <form action="{{ route('user.conversations.reply', $conversation ?? 0) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="message" placeholder="Type your message..." class="form-control">
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
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

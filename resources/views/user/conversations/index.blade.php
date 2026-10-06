<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Conversations') }}</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="list-group list-group-flush">
                    @forelse($conversations ?? [] as $conversation)
                    <a href="{{ route('user.conversations.show', $conversation) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <span class="avatar me-2">{{ substr($conversation->agent->name ?? 'S', 0, 1) }}</span>
                                <div>
                                    <div>{{ $conversation->agent->name ?? 'Support Agent' }}</div>
                                    <div class="text-secondary small">{{ Str::limit($conversation->last_message ?? 'No messages', 50) }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ ($conversation->status ?? '') === 'active' ? 'bg-green-lt' : 'bg-secondary-lt' }}">
                                    {{ ucfirst($conversation->status ?? 'Active') }}
                                </span>
                                <div class="text-secondary small mt-1">{{ $conversation->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="list-group-item">
                        <div class="empty">
                            <p class="empty-title">No conversations yet</p>
                            <p class="empty-subtitle">Your live chat conversations will appear here.</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

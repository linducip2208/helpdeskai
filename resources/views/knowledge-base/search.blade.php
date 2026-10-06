<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Search Results') }}</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="mb-3">
                <form action="{{ route('knowledge-base.search') }}" method="GET">
                    <div class="input-group input-group-lg">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search the knowledge base..." class="form-control">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>

            @if(request('q'))
            <p class="text-secondary mb-3">{{ ($results ?? collect())->count() }} result(s) for "<strong>{{ request('q') }}</strong>"</p>
            @endif

            @forelse($results ?? [] as $article)
            <a href="{{ route('knowledge-base.show', $article) }}" class="card card-link mb-2">
                <div class="card-body">
                    <h3 class="card-title">{{ $article->title }}</h3>
                    <p class="text-secondary mb-1">{{ Str::limit($article->excerpt ?? strip_tags($article->body ?? ''), 150) }}</p>
                    <div class="text-secondary small">{{ $article->category->name ?? 'General' }} &middot; Updated {{ wib($article->updated_at, 'd F Y', false) }}</div>
                </div>
            </a>
            @empty
            <div class="card">
                <div class="card-body">
                    <div class="empty">
                        @if(request('q'))
                        <p class="empty-title">No results found for "{{ request('q') }}"</p>
                        <p class="empty-subtitle">Try different keywords.</p>
                        @else
                        <p class="empty-title">Search the knowledge base</p>
                        <p class="empty-subtitle">Enter a search term to find articles.</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforelse

            <div class="card-footer d-flex justify-content-center mt-3">
                {{ ($results ?? collect())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

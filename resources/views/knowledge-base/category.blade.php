<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ $category->name ?? 'Category' }}</h2>
    </x-slot>

    <div class="mb-3">
        <a href="{{ route('knowledge-base.index') }}">&larr; Knowledge Base</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="list-group list-group-flush">
                    @forelse($articles ?? [] as $article)
                    <a href="{{ route('knowledge-base.show', $article) }}" class="list-group-item list-group-item-action">
                        <h3 class="card-title">{{ $article->title }}</h3>
                        <p class="text-secondary mb-1">{{ Str::limit($article->excerpt ?? '', 120) }}</p>
                        <div class="text-secondary small">Last updated {{ wib($article->updated_at, 'd F Y', false) }}</div>
                    </a>
                    @empty
                    <div class="list-group-item">
                        <div class="empty">
                            <p class="empty-title">No articles in this category</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

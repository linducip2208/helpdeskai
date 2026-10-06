<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Knowledge Base') }}</h2>
    </x-slot>

    <div class="row justify-content-center mb-3">
        <div class="col-12 col-lg-8">
            <form action="{{ route('knowledge-base.search') }}" method="GET">
                <div class="input-group input-group-lg">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search the knowledge base..." class="form-control">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row row-cards mb-3">
        @forelse($categories ?? [] as $category)
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="{{ route('knowledge-base.category', $category) }}" class="card card-link">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <span class="avatar me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </span>
                        <h3 class="card-title mb-0">{{ $category->name }}</h3>
                    </div>
                    <p class="text-secondary mb-0">{{ $category->articles_count ?? 0 }} articles</p>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="empty">
                        <p class="empty-title">No categories yet</p>
                    </div>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <h3 class="card-title mb-2">Popular Articles</h3>
    <div class="row row-cards">
        @forelse($featuredArticles ?? [] as $article)
        <div class="col-12 col-md-6">
            <a href="{{ route('knowledge-base.show', $article) }}" class="card card-link">
                <div class="card-body">
                    <h4 class="card-title">{{ $article->title }}</h4>
                    <p class="text-secondary mb-0">{{ Str::limit($article->excerpt ?? '', 80) }}</p>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="empty">
                        <p class="empty-title">No articles yet</p>
                    </div>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</x-app-layout>

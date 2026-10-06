<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ $article->title ?? 'Article' }}</h2>
    </x-slot>

    <div class="mb-3">
        <a href="{{ route('knowledge-base.index') }}">&larr; Knowledge Base</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <article class="card">
                <div class="card-body">
                    <h1 class="card-title fs-2">{{ $article->title }}</h1>
                    <div class="d-flex align-items-center text-secondary small mb-3">
                        <span>Updated {{ wib($article->updated_at, 'd F Y', false) }}</span>
                        @if($article->category)
                        <span class="mx-2">&middot;</span>
                        <a href="{{ route('knowledge-base.category', $article->category) }}">{{ $article->category->name }}</a>
                        @endif
                    </div>
                    <div>
                        {!! nl2br(e($article->body ?? '')) !!}
                    </div>

                    @if(($article->tags ?? false))
                    <div class="mt-3 pt-3 border-top">
                        <p class="text-secondary small mb-2">Tags:</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach(explode(',', $article->tags) as $tag)
                            <span class="badge bg-secondary-lt">{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </article>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
            <h2 class="page-title mb-0">{{ __('Blog') }}</h2>
            <a href="{{ route('blog.feed') }}" class="btn btn-sm btn-ghost-orange">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" class="me-1"><path d="M6.18 15.64a2.18 2.18 0 012.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 012.18-2.18M4 4.44A15.56 15.56 0 0119.56 20h-2.83A12.73 12.73 0 004 7.27V4.44m0 5.66a9.9 9.9 0 019.9 9.9h-2.83A7.07 7.07 0 004 12.93V10.1z"/></svg>
                RSS
            </a>
        </div>
    </x-slot>

    @if(($categories ?? collect())->count())
    <div class="d-flex flex-wrap gap-1 mb-3">
        <a href="{{ route('blog.index') }}"
           class="btn btn-sm {{ empty($activeCategory) ? 'btn-primary' : '' }}">
            All
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('blog.category', $cat) }}"
           class="btn btn-sm {{ ($activeCategory ?? null) === $cat ? 'btn-primary' : '' }}">
            {{ $cat }}
        </a>
        @endforeach
    </div>
    @endif

    <div class="row row-cards">
        @forelse($posts ?? [] as $post)
        <div class="col-12 col-md-6 col-lg-4">
            <article class="card">
                @if($post->featured_image ?? false)
                <div class="img-responsive img-responsive-16x9 card-img-top" style="background-image: url({{ $post->featured_image }})" title="{{ $post->title }}"></div>
                @endif
                <div class="card-body">
                    <div class="d-flex align-items-center text-secondary small mb-2">
                        <span>{{ wib($post->published_at, 'd F Y', false) }}</span>
                        @if($post->category)
                        <span class="mx-2">&middot;</span>
                        <span>{{ is_object($post->category) ? $post->category->name : $post->category }}</span>
                        @endif
                    </div>
                    <h3 class="card-title">
                        <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                    </h3>
                    <p class="text-secondary">{{ Str::limit($post->excerpt ?? '', 120) }}</p>
                    <a href="{{ route('blog.show', $post->slug) }}">Read more &rarr;</a>
                </div>
            </article>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="empty">
                        <p class="empty-title">No posts published yet</p>
                    </div>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <div class="card-footer d-flex justify-content-center mt-3">
        {{ ($posts ?? collect())->links() }}
    </div>
</x-app-layout>

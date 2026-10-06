<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ $post->title ?? 'Blog Post' }}</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <article class="card">
                @if($post->featured_image ?? false)
                <div class="img-responsive img-responsive-16x9 card-img-top" style="background-image: url({{ $post->featured_image }})" title="{{ $post->title }}"></div>
                @endif
                <div class="card-body">
                    <div class="d-flex align-items-center text-secondary small mb-3">
                        <span>{{ wib($post->published_at, 'd F Y', false) }}</span>
                        <span class="mx-2">&middot;</span>
                        <span>{{ $post->author->name ?? 'Admin' }}</span>
                        @if($post->category)
                        <span class="mx-2">&middot;</span>
                        <span>{{ is_object($post->category) ? $post->category->name : $post->category }}</span>
                        @endif
                    </div>
                    <h1 class="card-title fs-2">{{ $post->title }}</h1>
                    <div>
                        {!! nl2br(e($post->body ?? '')) !!}
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <a href="{{ route('blog.index') }}">&larr; Back to Blog</a>
                    </div>
                </div>
            </article>
        </div>
    </div>
</x-app-layout>

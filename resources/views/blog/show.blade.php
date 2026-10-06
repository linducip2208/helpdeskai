<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $post->title ?? 'Blog Post' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <article class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @if($post->featured_image ?? false)
                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-64 object-cover">
                @endif
                <div class="p-8">
                    <div class="flex items-center text-sm text-gray-400 mb-4">
                        <span>{{ $post->published_at?->format('M d, Y') }}</span>
                        <span class="mx-2">&middot;</span>
                        <span>{{ $post->author->name ?? 'Admin' }}</span>
                        @if($post->category)
                        <span class="mx-2">&middot;</span>
                        <span>{{ $post->category->name }}</span>
                        @endif
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">{{ $post->title }}</h1>
                    <div class="prose prose-lg max-w-none text-gray-700 dark:text-gray-300">
                        {!! nl2br(e($post->body ?? '')) !!}
                    </div>
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('blog.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">&larr; Back to Blog</a>
                    </div>
                </div>
            </article>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $article->title ?? 'Article' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('knowledge-base.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 mb-4 inline-block">&larr; Knowledge Base</a>

            <article class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ $article->title }}</h1>
                    <div class="flex items-center text-sm text-gray-400 mb-6">
                        <span>Updated {{ $article->updated_at->format('M d, Y') }}</span>
                        @if($article->category)
                        <span class="mx-2">&middot;</span>
                        <a href="{{ route('knowledge-base.category', $article->category) }}" class="text-indigo-600 hover:text-indigo-700">{{ $article->category->name }}</a>
                        @endif
                    </div>
                    <div class="prose prose-lg max-w-none text-gray-700 dark:text-gray-300">
                        {!! nl2br(e($article->body ?? '')) !!}
                    </div>

                    @if(($article->tags ?? false))
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-400 mb-2">Tags:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach(explode(',', $article->tags) as $tag)
                            <span class="inline-flex px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-xs text-gray-600 dark:text-gray-400">{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </article>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $category->name ?? 'Category' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('knowledge-base.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 mb-6 inline-block">&larr; Knowledge Base</a>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($articles ?? [] as $article)
                    <a href="{{ route('knowledge-base.show', $article) }}" class="block px-6 py-5 hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                        <h3 class="font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 transition">{{ $article->title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($article->excerpt ?? '', 120) }}</p>
                        <div class="text-xs text-gray-400 mt-2">Last updated {{ $article->updated_at->format('M d, Y') }}</div>
                    </a>
                    @empty
                    <div class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">No articles in this category.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

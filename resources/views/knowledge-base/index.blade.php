<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Knowledge Base') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <!-- Search -->
            <div class="mb-10">
                <form action="{{ route('knowledge-base.search') }}" method="GET" class="max-w-2xl mx-auto">
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search the knowledge base..."
                               class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-base py-3 pl-12 pr-4 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </form>
            </div>

            <!-- Categories -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                @forelse($categories ?? [] as $category)
                <a href="{{ route('knowledge-base.category', $category) }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition group">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 transition">{{ $category->name }}</h3>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $category->articles_count ?? 0 }} articles</p>
                </a>
                @empty
                <div class="col-span-full text-center py-8 text-gray-400 dark:text-gray-500">No categories yet.</div>
                @endforelse
            </div>

            <!-- Featured Articles -->
            <div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Popular Articles</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($featuredArticles ?? [] as $article)
                    <a href="{{ route('knowledge-base.show', $article) }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-5 hover:shadow-md transition">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 transition mb-1">{{ $article->title }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($article->excerpt ?? '', 80) }}</p>
                    </a>
                    @empty
                    <div class="col-span-full text-center py-6 text-gray-400 dark:text-gray-500">No articles yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Search Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Search -->
            <div class="mb-8">
                <form action="{{ route('knowledge-base.search') }}" method="GET">
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search the knowledge base..."
                               class="w-full rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-base py-3 pl-12 pr-4 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </form>
            </div>

            @if(request('q'))
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ ($results ?? collect())->count() }} result(s) for "<strong>{{ request('q') }}</strong>"</p>
            @endif

            <div class="space-y-4">
                @forelse($results ?? [] as $article)
                <a href="{{ route('knowledge-base.show', $article) }}" class="block bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 hover:text-indigo-600 transition">{{ $article->title }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($article->excerpt ?? strip_tags($article->body ?? ''), 150) }}</p>
                    <div class="text-xs text-gray-400 mt-2">{{ $article->category->name ?? 'General' }} &middot; Updated {{ $article->updated_at->format('M d, Y') }}</div>
                </a>
                @empty
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-12 text-center text-gray-400 dark:text-gray-500">
                    @if(request('q'))
                    No results found for "<strong>{{ request('q') }}</strong>". Try different keywords.
                    @else
                    Enter a search term to find articles.
                    @endif
                </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ ($results ?? collect())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

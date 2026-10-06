<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Blog') }}
            </h2>
            <a href="{{ route('blog.feed') }}" class="inline-flex items-center gap-1.5 text-sm text-orange-600 hover:text-orange-700">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.18 15.64a2.18 2.18 0 012.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 012.18-2.18M4 4.44A15.56 15.56 0 0119.56 20h-2.83A12.73 12.73 0 004 7.27V4.44m0 5.66a9.9 9.9 0 019.9 9.9h-2.83A7.07 7.07 0 004 12.93V10.1z"/></svg>
                RSS
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(($categories ?? collect())->count())
            <div class="flex flex-wrap gap-2 mb-8">
                <a href="{{ route('blog.index') }}"
                   class="px-3 py-1.5 rounded-full text-sm font-medium transition {{ empty($activeCategory) ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50' }}">
                    All
                </a>
                @foreach($categories as $cat)
                <a href="{{ route('blog.category', $cat) }}"
                   class="px-3 py-1.5 rounded-full text-sm font-medium transition {{ ($activeCategory ?? null) === $cat ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50' }}">
                    {{ $cat }}
                </a>
                @endforeach
            </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($posts ?? [] as $post)
                <article class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg transition hover:shadow-md">
                    @if($post->featured_image ?? false)
                    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                    @else
                    <div class="w-full h-48 bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>
                    @endif
                    <div class="p-6">
                        <div class="flex items-center text-xs text-gray-400 mb-3">
                            <span>{{ $post->published_at?->format('M d, Y') }}</span>
                            @if($post->category)
                            <span class="mx-2">&middot;</span>
                            <span>{{ is_object($post->category) ? $post->category->name : $post->category }}</span>
                            @endif
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-indigo-600 transition">{{ $post->title }}</a>
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($post->excerpt ?? '', 120) }}</p>
                        <a href="{{ route('blog.show', $post->slug) }}" class="inline-flex items-center mt-4 text-sm font-medium text-indigo-600 hover:text-indigo-700">Read more &rarr;</a>
                    </div>
                </article>
                @empty
                <div class="col-span-full text-center py-12 text-gray-400 dark:text-gray-500">No posts published yet.</div>
                @endforelse
            </div>
            <div class="mt-8">
                {{ ($posts ?? collect())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

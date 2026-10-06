<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Best Help Desk Software') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    <!-- JSON-LD Schema -->
                    <script type="application/ld+json">
                    {
                        "@@context": "https://schema.org",
                        "@type": "ItemList",
                        "name": "Best Help Desk Software {{ date('Y') }}",
                        "itemListElement": [
                            @foreach($items ?? [] as $i => $item)
                            {
                                "@type": "ListItem",
                                "position": {{ $i + 1 }},
                                "item": {
                                    "@type": "SoftwareApplication",
                                    "name": "{{ $item->name }}",
                                    "description": "{{ Str::limit(strip_tags($item->description ?? ''), 150) }}"
                                }
                            }@if(!$loop->last),@endif
                            @endforeach
                        ]
                    }
                    </script>

                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Best Help Desk Software {{ date('Y') }}</h1>
                    <p class="text-gray-500 dark:text-gray-400 mb-8">Top-rated help desk solutions to streamline your customer support operations.</p>

                    <div class="space-y-6">
                        @forelse($items ?? [] as $i => $item)
                        <div class="bg-gray-50 dark:bg-gray-750 rounded-xl p-6 border border-gray-100 dark:border-gray-700">
                            <div class="flex items-start">
                                <span class="flex-shrink-0 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center font-bold text-sm mr-4 mt-0.5">{{ $i + 1 }}</span>
                                <div class="flex-1">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $item->name }}</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ $item->description ?? 'A comprehensive help desk solution with powerful features.' }}</p>
                                    <div class="flex flex-wrap gap-2 text-xs">
                                        <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-full">Feature-rich</span>
                                        <span class="px-2 py-1 bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 rounded-full">AI-Powered</span>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <div class="flex items-center space-x-1 text-amber-400">
                                        @for($s = 0; $s < 5; $s++)
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                        @endfor
                                    </div>
                                    <span class="block text-xs text-gray-400 mt-1 text-right">{{ $item->rating ?? '4.5' }}/5</span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-400">
                            <p>Coming soon! Check back for our curated list of the best help desk software.</p>
                        </div>
                        @endforelse
                    </div>

                    <div class="mt-10 prose prose-sm max-w-none text-gray-600 dark:text-gray-400">
                        <h3>How to Choose the Best Help Desk Software</h3>
                        <p>When evaluating help desk software, consider these key factors: ticket management capabilities, automation features, reporting and analytics, integration options, AI-powered tools, scalability, and pricing. The best solution depends on your team size, support volume, and specific workflow requirements.</p>
                        <h3>Why AI-Powered Help Desks Are Leading</h3>
                        <p>AI-powered help desks leverage machine learning to automatically categorize tickets, suggest responses, detect sentiment, and route issues to the right agents — dramatically reducing response times and improving customer satisfaction scores.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

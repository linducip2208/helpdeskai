<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Alternatives to') }} {{ $product->name ?? 'Popular Help Desk Software' }}
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
                        "name": "Top Alternatives to {{ $product->name ?? 'Popular Help Desk Software' }} {{ date('Y') }}",
                        "itemListElement": [
                            @foreach($alternatives ?? [] as $i => $alt)
                            {
                                "@type": "ListItem",
                                "position": {{ $i + 1 }},
                                "item": { "@type": "SoftwareApplication", "name": "{{ $alt->name }}" }
                            }@if(!$loop->last),@endif
                            @endforeach
                        ]
                    }
                    </script>

                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Top Alternatives to {{ $product->name ?? 'Popular Help Desk Software' }} in {{ date('Y') }}</h1>
                    <p class="text-gray-500 dark:text-gray-400 mb-8">Looking for {{ $product->name ?? '' }} alternatives? Here are the best options to consider.</p>

                    <div class="space-y-5">
                        @forelse($alternatives ?? [] as $i => $alt)
                        <div class="bg-gray-50 dark:bg-gray-750 rounded-xl p-6 border border-gray-100 dark:border-gray-700">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">#{{ $i + 1 }} Alternative</span>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mt-1">{{ $alt->name }}</h2>
                                </div>
                                <div class="flex items-center space-x-1 text-amber-400">
                                    @for($s = 0; $s < 5; $s++)
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ $alt->description ?? 'A powerful alternative with competitive features and pricing.' }}</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="text-xs px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full">{{ $alt->pricing_tier ?? 'Free trial available' }}</span>
                                <span class="text-xs px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full">{{ $alt->best_for ?? 'SMB to Enterprise' }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-400">
                            <p>We're curating the best alternatives. Check back soon!</p>
                        </div>
                        @endforelse
                    </div>

                    <div class="mt-10 prose prose-sm max-w-none text-gray-600 dark:text-gray-400">
                        <h3>Why Consider Alternatives?</h3>
                        <p>While {{ $product->name ?? 'popular help desk solutions' }} offer great features, alternatives may provide better pricing, more specialized tools, or better integration with your existing stack.</p>
                        <h3>How to Evaluate Alternatives</h3>
                        <p>Consider factors like AI capabilities, automation depth, team collaboration features, reporting, integration ecosystem, and total cost of ownership when evaluating help desk alternatives.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

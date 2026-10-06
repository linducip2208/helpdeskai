<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Alternatives to') }} {{ $product->name ?? 'Popular Help Desk Software' }}</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
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

                    <h1 class="card-title fs-2">Top Alternatives to {{ $product->name ?? 'Popular Help Desk Software' }} in {{ date('Y') }}</h1>
                    <p class="text-secondary mb-3">Looking for {{ $product->name ?? '' }} alternatives? Here are the best options to consider.</p>

                    @forelse($alternatives ?? [] as $i => $alt)
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div>
                                    <span class="badge bg-blue-lt">#{{ $i + 1 }} Alternative</span>
                                    <h2 class="card-title mt-1 mb-0">{{ $alt->name }}</h2>
                                </div>
                                <div class="text-yellow">
                                    @for($s = 0; $s < 5; $s++)
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-secondary small mb-2">{{ $alt->description ?? 'A powerful alternative with competitive features and pricing.' }}</p>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-secondary-lt">{{ $alt->pricing_tier ?? 'Free trial available' }}</span>
                                <span class="badge bg-secondary-lt">{{ $alt->best_for ?? 'SMB to Enterprise' }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="empty">
                        <p class="empty-title">No alternatives yet</p>
                        <p class="empty-subtitle">We're curating the best alternatives. Check back soon!</p>
                    </div>
                    @endforelse

                    <div class="mt-3 text-secondary">
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

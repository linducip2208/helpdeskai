<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Best Help Desk Software') }}</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
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

                    <h1 class="card-title fs-2">Best Help Desk Software {{ date('Y') }}</h1>
                    <p class="text-secondary mb-3">Top-rated help desk solutions to streamline your customer support operations.</p>

                    @forelse($items ?? [] as $i => $item)
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <span class="avatar me-2">{{ $i + 1 }}</span>
                                <div class="flex-fill">
                                    <h2 class="card-title mb-1">{{ $item->name }}</h2>
                                    <p class="text-secondary small mb-2">{{ $item->description ?? 'A comprehensive help desk solution with powerful features.' }}</p>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-blue-lt">Feature-rich</span>
                                        <span class="badge bg-green-lt">AI-Powered</span>
                                    </div>
                                </div>
                                <div class="ms-3 text-end flex-shrink-0">
                                    <div class="text-yellow">
                                        @for($s = 0; $s < 5; $s++)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                        @endfor
                                    </div>
                                    <span class="text-secondary small">{{ $item->rating ?? '4.5' }}/5</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="empty">
                        <p class="empty-title">Coming soon</p>
                        <p class="empty-subtitle">Check back for our curated list of the best help desk software.</p>
                    </div>
                    @endforelse

                    <div class="mt-3 text-secondary">
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

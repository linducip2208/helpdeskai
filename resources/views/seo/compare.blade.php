<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Compare Help Desk Software') }}</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <script type="application/ld+json">
                    {
                        "@@context": "https://schema.org",
                        "@type": "FAQPage",
                        "mainEntity": [{
                            "@type": "Question",
                            "name": "{{ $itemA->name ?? 'Product A' }} vs {{ $itemB->name ?? 'Product B' }}: Which is better?",
                            "acceptedAnswer": {
                                "@type": "Answer",
                                "text": "Compare features, pricing, and capabilities of {{ $itemA->name ?? 'Product A' }} and {{ $itemB->name ?? 'Product B' }} to find the best fit for your team."
                            }
                        }]
                    }
                    </script>

                    <h1 class="card-title fs-2">{{ $itemA->name ?? 'Product A' }} vs {{ $itemB->name ?? 'Product B' }}</h1>
                    <p class="text-secondary mb-3">A detailed head-to-head comparison to help you choose the right help desk solution.</p>

                    <div class="table-responsive mb-3">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Feature</th>
                                    <th class="text-center">{{ $itemA->name ?? 'Product A' }}</th>
                                    <th class="text-center">{{ $itemB->name ?? 'Product B' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $features = ['Ticket Management', 'AI Automation', 'Live Chat', 'Reporting', 'Multi-Channel', 'SLA Management', 'Knowledge Base', 'API Access', 'Custom Branding']; @endphp
                                @foreach($features as $i => $feature)
                                <tr>
                                    <td>{{ $feature }}</td>
                                    <td class="text-center">
                                        @if($i % 2 === 0)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M5 13l4 4L19 7"/></svg>
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-danger"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($i % 3 !== 2)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M5 13l4 4L19 7"/></svg>
                                        @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-danger"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-secondary">
                        <h3>Overview</h3>
                        <p>Both {{ $itemA->name ?? 'Product A' }} and {{ $itemB->name ?? 'Product B' }} offer robust help desk solutions. Your choice depends on specific requirements like team size, budget, and integration needs.</p>

                        <h3>{{ $itemA->name ?? 'Product A' }} Strengths</h3>
                        <p>{{ $itemA->name ?? 'Product A' }} excels in AI-powered automation and customizable workflows, making it ideal for growing support teams that need scalable solutions.</p>

                        <h3>{{ $itemB->name ?? 'Product B' }} Strengths</h3>
                        <p>{{ $itemB->name ?? 'Product B' }} offers an intuitive interface and strong multi-channel support, perfect for teams prioritizing ease of use and omnichannel communication.</p>

                        <h3>Pricing Comparison</h3>
                        <p>Both platforms offer tiered pricing. {{ $itemA->name ?? 'Product A' }} starts with a more affordable entry-level plan, while {{ $itemB->name ?? 'Product B' }} includes more features in their mid-tier plan. Evaluate based on your team's size and feature requirements.</p>

                        <h3>Verdict</h3>
                        <p>Choose {{ $itemA->name ?? 'Product A' }} if AI automation and customization are your priorities. Choose {{ $itemB->name ?? 'Product B' }} if you need a straightforward, multi-channel solution with minimal setup time.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

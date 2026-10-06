<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Compare Help Desk Software') }}
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

                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ $itemA->name ?? 'Product A' }} vs {{ $itemB->name ?? 'Product B' }}</h1>
                    <p class="text-gray-500 dark:text-gray-400 mb-8">A detailed head-to-head comparison to help you choose the right help desk solution.</p>

                    <!-- Comparison Table -->
                    <div class="overflow-x-auto mb-8">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-750 rounded-tl-xl">Feature</th>
                                    <th class="px-6 py-3 text-center text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-gray-50 dark:bg-gray-750">{{ $itemA->name ?? 'Product A' }}</th>
                                    <th class="px-6 py-3 text-center text-sm font-medium text-emerald-600 dark:text-emerald-400 bg-gray-50 dark:bg-gray-750 rounded-tr-xl">{{ $itemB->name ?? 'Product B' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @php $features = ['Ticket Management', 'AI Automation', 'Live Chat', 'Reporting', 'Multi-Channel', 'SLA Management', 'Knowledge Base', 'API Access', 'Custom Branding']; @endphp
                                @foreach($features as $feature)
                                <tr>
                                    <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $feature }}</td>
                                    <td class="px-6 py-3 text-center">
                                        @if(rand(0,1))
                                        <svg class="w-5 h-5 mx-auto text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                        <svg class="w-5 h-5 mx-auto text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        @if(rand(0,1))
                                        <svg class="w-5 h-5 mx-auto text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                        <svg class="w-5 h-5 mx-auto text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="prose prose-sm max-w-none text-gray-600 dark:text-gray-400">
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

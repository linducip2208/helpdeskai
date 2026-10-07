<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Support & Licensing') }}</h2>
    </x-slot>

    <div class="row row-cards">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ __('Commercial License') }}</h3></div>
                <div class="card-body">
                    <p class="text-muted">{{ __(':app is commercial proprietary software. Production use requires a valid license bound to your domain.', ['app' => $appName]) }}</p>
                    <p class="mb-0">{{ __('For license purchase, renewal, and commercial inquiries:') }}</p>
                    <p class="h2 mt-1">{{ $salesContact }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">{{ __('Support & Updates') }}</h3></div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>{{ __('Self-help first: search the Knowledge Base for guides and troubleshooting.') }}</li>
                        <li>{{ __('Report bugs with steps to reproduce, expected vs actual behavior, and your app version.') }}</li>
                        <li>{{ __('Update entitlement and response targets depend on your purchased package.') }}</li>
                    </ul>
                    <div class="mt-3">
                        <a href="{{ route('knowledge-base.index') }}" class="btn">{{ __('Knowledge Base') }}</a>
                        <a href="{{ route('contact') }}" class="btn btn-primary">{{ __('Contact Us') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Our Services') }}</h2>
    </x-slot>

    <div class="row row-cards">
        @forelse($services ?? [] as $service)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <span class="avatar mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <h3 class="card-title">
                        <a href="{{ route('services.show', $service) }}">{{ $service->name }}</a>
                    </h3>
                    <p class="text-secondary mb-3">{{ Str::limit($service->description ?? '', 100) }}</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="fw-bold">
                            {{ $service->price ? '$'.number_format($service->price, 2) : 'Free' }}
                        </span>
                        <a href="{{ route('services.show', $service) }}" class="btn btn-sm">Learn more &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="empty">
                        <p class="empty-title">No services available</p>
                        <p class="empty-subtitle">Please check back later.</p>
                    </div>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</x-app-layout>

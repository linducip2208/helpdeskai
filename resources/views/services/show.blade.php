<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ $service->name ?? 'Service Detail' }}</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h1 class="card-title fs-2 mb-0">{{ $service->name }}</h1>
                        <span class="fw-bold fs-3">
                            {{ $service->price ? '$'.number_format($service->price, 2) : 'Free' }}
                        </span>
                    </div>
                    @if($service->duration ?? false)
                    <div class="text-secondary small mb-3">Duration: {{ $service->duration }}</div>
                    @endif
                    <div>
                        {!! nl2br(e($service->description ?? '')) !!}
                    </div>
                    <div class="mt-3 pt-3 border-top">
                        <a href="{{ route('services.index') }}">&larr; All Services</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

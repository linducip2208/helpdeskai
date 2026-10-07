<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Notification preferences') }}</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    @include('profile.partials.notification-preferences')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

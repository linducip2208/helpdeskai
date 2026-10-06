<x-guest-layout>
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">{{ __('Confirm password') }}</h2>
            <p class="text-muted mb-4">{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</p>

            <form method="POST" action="{{ route('password.confirm') }}" autocomplete="off" novalidate>
                @csrf

                <div class="mb-3">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <div class="form-footer">
                    <x-primary-button class="w-100">
                        {{ __('Confirm') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>

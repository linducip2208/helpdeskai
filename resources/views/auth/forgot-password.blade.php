<x-guest-layout>
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">{{ __('Forgot password') }}</h2>
            <p class="text-muted mb-4">{{ __('Enter your email address and we will email you a password reset link.') }}</p>

            <x-auth-session-status class="mb-3" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" autocomplete="off" novalidate>
                @csrf

                <div class="mb-3">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus placeholder="{{ __('your@email.com') }}" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="form-footer">
                    <x-primary-button class="w-100">
                        {{ __('Email Password Reset Link') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center text-muted mt-3">
        <a href="{{ route('login') }}">{{ __('Back to login') }}</a>
    </div>
</x-guest-layout>

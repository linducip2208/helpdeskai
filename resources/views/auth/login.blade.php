<x-guest-layout>
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">{{ __('Login to your account') }}</h2>

            <x-auth-session-status class="mb-3" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" autocomplete="off" novalidate>
                @csrf

                <div class="mb-3">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="{{ __('your@email.com') }}" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        {{ __('Password') }}
                        @if (Route::has('password.request'))
                            <span class="form-label-description">
                                <a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                            </span>
                        @endif
                    </label>
                    <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="{{ __('Your password') }}" />
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <div class="mb-3">
                    <label class="form-check">
                        <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                        <span class="form-check-label">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="form-footer">
                    <x-primary-button class="w-100">
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center text-muted mt-3">
        {{ __("Don't have an account yet?") }} <a href="{{ route('register') }}" tabindex="-1">{{ __('Register') }}</a>
    </div>
</x-guest-layout>

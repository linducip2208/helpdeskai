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
            @if(\App\Http\Controllers\Auth\SocialLoginController::enabled())
            <div class="text-center text-muted my-3">{{ __('or continue with') }}</div>
            <a href="{{ route('login.google') }}" class="btn w-100">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.31 9.14 5.38 12 5.38z"/></svg>
                {{ __('Continue with Google') }}
            </a>
            @endif
        </div>
    </div>
    <div class="text-center text-muted mt-3">
        {{ __("Don't have an account yet?") }} <a href="{{ route('register') }}" tabindex="-1">{{ __('Register') }}</a>
    </div>
</x-guest-layout>

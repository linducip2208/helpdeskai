<x-guest-layout>
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">{{ __('Create new account') }}</h2>

            <form method="POST" action="{{ route('register') }}" autocomplete="off" novalidate>
                @csrf

                <div class="mb-3">
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="{{ __('Your name') }}" />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="{{ __('your@email.com') }}" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="{{ __('Your password') }}" />
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('Confirm password') }}" />
                    <x-input-error :messages="$errors->get('password_confirmation')" />
                </div>

                <div class="form-footer">
                    <x-primary-button class="w-100">
                        {{ __('Register') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center text-muted mt-3">
        {{ __('Already registered?') }} <a href="{{ route('login') }}" tabindex="-1">{{ __('Log in') }}</a>
    </div>
</x-guest-layout>

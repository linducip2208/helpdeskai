<x-guest-layout>
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">{{ __('Verify your email') }}</h2>
            <p class="text-muted mb-4">{{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}</p>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success" role="alert">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <x-primary-button>
                        {{ __('Resend Verification Email') }}
                    </x-primary-button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-link">{{ __('Log Out') }}</button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>

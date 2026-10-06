<x-guest-layout>
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-1">{{ __('Two-Factor Verification') }}</h2>
            <p class="text-muted text-center mb-4">{{ __('Enter the 6-digit code from your authenticator app, or one of your recovery codes.') }}</p>

            <form method="POST" action="{{ route('two-factor.verify') }}" autocomplete="off">
                @csrf
                <div class="mb-3">
                    <input type="text" name="code" autocomplete="one-time-code" autofocus required
                        placeholder="123456" inputmode="numeric"
                        class="form-control form-control-lg text-center font-monospace">
                    @error('code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Verify') }}</button>
                </div>
            </form>

            <div class="text-center mt-3">
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm">{{ __('Sign out') }}</button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>

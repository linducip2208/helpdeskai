<x-guest-layout>
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-1">{{ __('Enable Two-Factor Authentication') }}</h2>
            <p class="text-muted text-center mb-4">{{ __('Scan the QR code with your authenticator app, then enter the 6-digit code below to confirm.') }}</p>

            <div class="d-flex flex-column align-items-center gap-2 mb-4">
                <img src="{{ $qrUrl }}" alt="{{ __('2FA QR Code') }}" class="rounded border" width="200" height="200">
                <div class="text-muted small">{{ __('Or enter manually:') }}</div>
                <code class="font-monospace user-select-all">{{ $secret }}</code>
            </div>

            <form method="POST" action="{{ route('two-factor.confirm') }}" autocomplete="off">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('6-digit code') }}</label>
                    <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required
                        pattern="\d{6}" maxlength="6"
                        class="form-control form-control-lg text-center font-monospace">
                    @error('code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Confirm & Enable') }}</button>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('profile.edit') }}" class="btn btn-link btn-sm">{{ __('Cancel') }}</a>
            </div>
        </div>
    </div>
</x-guest-layout>

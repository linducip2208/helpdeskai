<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">Two-Factor Authentication</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            @if (session('status'))
                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h3 class="card-title mb-0">2FA is enabled</h3>
                        <span class="badge bg-green-lt">Active</span>
                    </div>
                    <p class="text-secondary mb-3">Your account is protected. We'll ask for a 6-digit code on every new sign-in.</p>

                    @if ($recoveryCodes ?? session('two_factor_recovery_codes'))
                        @php $codes = $recoveryCodes ?? session('two_factor_recovery_codes'); @endphp
                        <div class="alert alert-warning mb-3">
                            <p class="mb-1"><strong>Save these recovery codes</strong></p>
                            <p class="small mb-2">Each code can be used once if you lose access to your authenticator. Store them somewhere safe — they won't be shown again.</p>
                            <div class="row g-2 font-monospace">
                                @foreach ($codes as $code)
                                    <div class="col-6"><code class="d-block border rounded px-2 py-1">{{ $code }}</code></div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('two-factor.recovery') }}">
                            @csrf
                            <button type="submit" class="btn">
                                Regenerate recovery codes
                            </button>
                        </form>

                        <form method="POST" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('Disable 2FA? Your account will be less secure.')">
                            @csrf
                            @method('DELETE')
                            <div class="d-flex align-items-center gap-2">
                                <input type="password" name="password" placeholder="Current password" required class="form-control">
                                <button type="submit" class="btn btn-danger">
                                    Disable 2FA
                                </button>
                            </div>
                            @error('password')<p class="text-danger small mt-1 mb-0">{{ $message }}</p>@enderror
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

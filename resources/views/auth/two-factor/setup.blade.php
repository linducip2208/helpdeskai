<x-guest-layout>
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Enable Two-Factor Authentication</h2>
    <p class="text-sm text-gray-600 mb-4">Scan the QR code with your authenticator app (Google Authenticator, Authy, 1Password, etc.), then enter the 6-digit code below to confirm.</p>

    <div class="flex flex-col items-center gap-3 mb-4">
        <img src="{{ $qrUrl }}" alt="2FA QR Code" class="rounded-lg border border-gray-200">
        <div class="text-xs text-gray-500">Or enter manually:</div>
        <code class="text-sm font-mono bg-gray-100 px-3 py-1 rounded select-all">{{ $secret }}</code>
    </div>

    <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-3">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">6-digit code</label>
            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required
                pattern="\d{6}" maxlength="6"
                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 tracking-widest text-center text-lg">
            @error('code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-md">
            Confirm & Enable
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
    </div>
</x-guest-layout>

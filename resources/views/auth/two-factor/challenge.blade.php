<x-guest-layout>
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Two-Factor Verification</h2>
    <p class="text-sm text-gray-600 mb-4">Enter the 6-digit code from your authenticator app, or one of your recovery codes.</p>

    <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-3">
        @csrf
        <div>
            <input type="text" name="code" autocomplete="one-time-code" autofocus required
                placeholder="123456 or recovery code"
                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 tracking-widest text-center text-lg">
            @error('code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-md">
            Verify
        </button>
    </form>

    <div class="mt-4 text-center">
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">Sign out</button>
        </form>
    </div>
</x-guest-layout>

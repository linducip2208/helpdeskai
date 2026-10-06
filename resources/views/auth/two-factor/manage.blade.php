<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Two-Factor Authentication</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-green-50 border border-green-200 rounded-md text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-medium text-gray-900">2FA is enabled</h3>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                </div>
                <p class="text-sm text-gray-600 mb-4">Your account is protected. We'll ask for a 6-digit code on every new sign-in.</p>

                @if ($recoveryCodes ?? session('two_factor_recovery_codes'))
                    @php $codes = $recoveryCodes ?? session('two_factor_recovery_codes'); @endphp
                    <div class="bg-amber-50 border border-amber-200 rounded-md p-4 mb-4">
                        <p class="text-sm font-medium text-amber-900 mb-2">Save these recovery codes</p>
                        <p class="text-xs text-amber-800 mb-3">Each code can be used once if you lose access to your authenticator. Store them somewhere safe — they won't be shown again.</p>
                        <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                            @foreach ($codes as $code)
                                <code class="bg-white px-3 py-2 rounded border border-amber-200">{{ $code }}</code>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('two-factor.recovery') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Regenerate recovery codes
                        </button>
                    </form>

                    <form method="POST" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('Disable 2FA? Your account will be less secure.')">
                        @csrf
                        @method('DELETE')
                        <div class="flex items-center gap-2">
                            <input type="password" name="password" placeholder="Current password" required
                                class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                                Disable 2FA
                            </button>
                        </div>
                        @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

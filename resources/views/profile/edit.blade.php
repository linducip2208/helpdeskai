<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl space-y-3">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Two-Factor Authentication</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Add an extra layer of security by requiring a 6-digit code from your authenticator app at sign-in.</p>
                    </header>

                    @if (auth()->user()->two_factor_confirmed_at)
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Enabled</span>
                            <a href="{{ route('two-factor.manage') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Manage</a>
                        </div>
                    @else
                        <a href="{{ route('two-factor.setup') }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md">
                            Enable 2FA
                        </a>
                    @endif
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg"
                 x-data="{ status: 'checking', loading: false, err: '' }"
                 x-init="status = await HelpDeskPush.pushStatus()">
                <div class="max-w-xl space-y-3">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Push Notifications</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Get notified in your browser when a new reply or ticket assignment comes in.</p>
                    </header>

                    <template x-if="status === 'unsupported'">
                        <p class="text-sm text-gray-500">Your browser does not support push notifications.</p>
                    </template>

                    <template x-if="status === 'denied'">
                        <p class="text-sm text-red-600">Notifications are blocked. Enable them in your browser site settings to continue.</p>
                    </template>

                    <template x-if="status === 'unsubscribed'">
                        <button type="button"
                            :disabled="loading"
                            @click="loading = true; err = ''; try { await HelpDeskPush.subscribePush(); status = 'subscribed'; } catch (e) { err = e.message; } finally { loading = false; }"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md disabled:opacity-50">
                            Enable Push
                        </button>
                    </template>

                    <template x-if="status === 'subscribed'">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                            <button type="button"
                                @click="loading = true; await HelpDeskPush.testPush(); loading = false;"
                                class="px-3 py-1.5 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                Send test
                            </button>
                            <button type="button"
                                @click="loading = true; await HelpDeskPush.unsubscribePush(); status = 'unsubscribed'; loading = false;"
                                class="px-3 py-1.5 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                Disable
                            </button>
                        </div>
                    </template>

                    <p x-show="err" x-text="err" class="text-sm text-red-600"></p>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">{{ __('Profile') }}</h2>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h3 class="card-title">Two-Factor Authentication</h3>
                    <p class="text-secondary mb-3">Add an extra layer of security by requiring a 6-digit code from your authenticator app at sign-in.</p>

                    @if (auth()->user()->two_factor_confirmed_at)
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-green-lt">Enabled</span>
                            <a href="{{ route('two-factor.manage') }}">Manage</a>
                        </div>
                    @else
                        <a href="{{ route('two-factor.setup') }}" class="btn btn-primary">
                            Enable 2FA
                        </a>
                    @endif
                </div>
            </div>

            <div class="card mb-3"
                 x-data="{ status: 'checking', loading: false, err: '' }"
                 x-init="status = await HelpDeskPush.pushStatus()">
                <div class="card-body">
                    <h3 class="card-title">Push Notifications</h3>
                    <p class="text-secondary mb-3">Get notified in your browser when a new reply or ticket assignment comes in.</p>

                    <template x-if="status === 'unsupported'">
                        <p class="text-secondary">Your browser does not support push notifications.</p>
                    </template>

                    <template x-if="status === 'denied'">
                        <p class="text-danger">Notifications are blocked. Enable them in your browser site settings to continue.</p>
                    </template>

                    <template x-if="status === 'unsubscribed'">
                        <button type="button"
                            :disabled="loading"
                            @click="loading = true; err = ''; try { await HelpDeskPush.subscribePush(); status = 'subscribed'; } catch (e) { err = e.message; } finally { loading = false; }"
                            class="btn btn-primary" :disabled="loading">
                            Enable Push
                        </button>
                    </template>

                    <template x-if="status === 'subscribed'">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge bg-green-lt">Active</span>
                            <button type="button"
                                @click="loading = true; await HelpDeskPush.testPush(); loading = false;"
                                class="btn">
                                Send test
                            </button>
                            <button type="button"
                                @click="loading = true; await HelpDeskPush.unsubscribePush(); status = 'unsubscribed'; loading = false;"
                                class="btn">
                                Disable
                            </button>
                        </div>
                    </template>

                    <p x-show="err" x-text="err" class="text-danger small"></p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

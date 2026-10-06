<section>
    <h3 class="card-title">{{ __('Delete Account') }}</h3>

    <p class="text-secondary mb-3">
        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
    </p>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <div class="modal-body">
                <h3 class="modal-title">{{ __('Are you sure you want to delete your account?') }}</h3>

                <p class="text-secondary">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>

                <div class="mt-3">
                    <x-input-label for="password" value="{{ __('Password') }}" />

                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="{{ __('Password') }}"
                    />

                    <x-input-error :messages="$errors->userDeletion->get('password')" />
                </div>
            </div>

            <div class="modal-footer">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>

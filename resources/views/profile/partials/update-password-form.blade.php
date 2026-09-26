<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-slate-300">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form
        method="post"
        action="{{ route('password.update') }}"
        class="mt-6 space-y-6"
        x-data="{ samePasswordError: '', isDirty: false }"
        x-init="
            const form = $el;
            const trackedFields = Array.from(form.querySelectorAll('input, select, textarea')).filter((field) => field.name && !field.disabled && field.type !== 'hidden');
            const initialValues = new Map(trackedFields.map((field) => [field.name, field.value]));
            const evaluateDirty = () => {
                isDirty = trackedFields.some((field) => field.value !== initialValues.get(field.name));
            };
            trackedFields.forEach((field) => field.addEventListener('input', evaluateDirty));
            trackedFields.forEach((field) => field.addEventListener('change', evaluateDirty));
            evaluateDirty();
        "
        x-on:submit="
            const currentPassword = $refs.current_password.value;
            const newPassword = $refs.new_password.value;

            if (currentPassword && newPassword && currentPassword === newPassword) {
                samePasswordError = 'Please choose a new password that is different from your old password.';
                $event.preventDefault();
                return;
            }

            samePasswordError = '';
        "
    >
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" x-ref="current_password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" x-ref="new_password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            <p x-show="samePasswordError" x-text="samePasswordError" class="form-error"></p>
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button x-bind:disabled="!isDirty">{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-slate-300"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

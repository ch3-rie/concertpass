<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-slate-300">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form
        method="post"
        action="{{ route('profile.update') }}"
        class="mt-6 space-y-6"
        x-data="{ isDirty: false }"
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
    >
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

        </div>

        <div class="flex items-center gap-4">
            <x-primary-button x-bind:disabled="!isDirty">{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
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

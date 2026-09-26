<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Set your new password below to complete the reset process.') }}
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.store') }}" x-data="{ processing: false }" @submit="processing = true">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">

        <div>
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a href="{{ route('password.request') }}" class="text-sm text-slate-500 hover:text-violet-400 transition">
                {{ __('Back') }}
            </a>

            <x-primary-button x-bind:disabled="processing" x-bind:class="{ 'opacity-60 cursor-not-allowed': processing }">
                <span x-show="!processing">{{ __('Reset Password') }}</span>
                <span x-show="processing" style="display: none;">{{ __('Resetting...') }}</span>
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Forgot your password? Enter your email address and we will simulate sending a reset link.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" x-data="{ processing: false }" @submit="processing = true">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-violet-400 transition">
                {{ __('Back to login') }}
            </a>

            <x-primary-button x-bind:disabled="processing" x-bind:class="{ 'opacity-60 cursor-not-allowed': processing }">
                <span x-show="!processing">{{ __('Send Reset Link') }}</span>
                <span x-show="processing" style="display: none;">{{ __('Sending...') }}</span>
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold uppercase tracking-[0.2em] text-white">Welcome Back</h1>
            <p class="mt-3 text-sm text-slate-400">Please enter your details to access your account.</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div class="form-group">
                <x-input-label for="email" :value="__('Email Address')" class="mb-2 block text-[11px] uppercase tracking-[0.1em] text-violet-400 font-bold" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" placeholder="Enter your email address" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-400" />
            </div>

            <div class="form-group" x-data="{ show: false }">
                <div class="flex items-center justify-between mb-2">
                    <x-input-label for="password" :value="__('Password')" class="block text-[11px] uppercase tracking-[0.1em] text-violet-400 font-bold" />
                    @if (Route::has('password.request'))
                        <a class="text-[10px] uppercase tracking-wider text-slate-500 hover:text-violet-400 transition font-bold" href="{{ route('password.request') }}">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <div class="relative flex items-center">
                    <x-text-input 
                        id="password" 
                        x-bind:type="show ? 'text' : 'password'" 
                        type="password"
                        name="password"
                        placeholder="Enter your password"
                        required 
                        autocomplete="current-password"
                        class="block w-full"
                        style="padding-right: 3.5rem !important;"
                    />

                    <button 
                        type="button" 
                        @click="show = !show" 
                        class="absolute right-0 top-0 bottom-0 px-4 flex items-center text-slate-500 hover:text-violet-400 transition-colors focus:outline-none z-10"
                    >
                        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-400" />
            </div>

            <div class="block mb-4"> 
                <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                    <input id="remember_me" type="checkbox" class="rounded border-white/10 bg-white/5 text-violet-500 focus:ring-violet-500" name="remember">
                    <span class="text-xs uppercase tracking-wide">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="pt-4">
                <x-primary-button class="w-full neon-pulse">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>

        <p class="text-center text-sm text-slate-400 pt-2">
            Don't have an account? <a href="{{ route('register') }}" class="text-violet-400 hover:text-white font-semibold">Create an account</a>
        </p>
    </div>
</x-guest-layout>
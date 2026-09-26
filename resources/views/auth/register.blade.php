<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold uppercase tracking-[0.2em] text-white">Create your account</h1>
            <p class="mt-3 text-sm text-slate-400">Join the modern ticket reservation platform and start booking premium seats.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div class="form-group">
                <x-input-label for="name" :value="__('Full Name')" class="mb-2 block text-[11px] uppercase tracking-[0.1em] text-violet-400 font-bold" />
                <x-text-input id="name" type="text" name="name" :value="old('name')" placeholder="Enter your full name" required autofocus autocomplete="name" class="w-full" />
                <x-input-error :messages="$errors->get('name')" class="mt-1 text-sm text-rose-400" />
            </div>

            <div class="form-group">
                <x-input-label for="email" :value="__('Email Address')" class="mb-2 block text-[11px] uppercase tracking-[0.1em] text-violet-400 font-bold" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" placeholder="Enter your email address" required autocomplete="username" class="w-full" />
                <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-rose-400" />
            </div>

            <div class="form-group" x-data="{ show: false }">
    <x-input-label for="password" :value="__('Password')" class="mb-2 block text-[11px] uppercase tracking-[0.1em] text-violet-400 font-bold" />
    
    <div class="relative flex items-center"> 
       <x-text-input 
            id="password" 
            x-bind:type="show ? 'text' : 'password'" 
            type="password" 
            name="password" 
            placeholder="Enter your password" 
            required 
            class="w-full"
            style="padding-right: 3.5rem !important;" 
        />
        
        <button 
            type="button" 
            @click="show = !show" 
            class="absolute right-0 top-0 bottom-0 px-4 flex items-center text-slate-500 hover:text-violet-400 transition-colors focus:outline-none z-10"
        >
            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
            </svg>
        </button>
    </div>
    <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-rose-400" />
</div>

<div class="form-group" x-data="{ show: false }">
    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="mb-2 block text-[11px] uppercase tracking-[0.1em] text-violet-400 font-bold" />
    
    <div class="relative flex items-center">
        <x-text-input 
            id="password_confirmation" 
            x-bind:type="show ? 'text' : 'password'" 
            type="password" 
            name="password_confirmation" 
            placeholder="Confirm your password" 
            required 
            autocomplete="new-password" 
            class="w-full" 
            style="padding-right: 3.5rem !important;" 
        />
        <button 
            type="button" 
            @click="show = !show" 
            class="absolute right-0 top-0 bottom-0 px-4 flex items-center text-slate-500 hover:text-violet-400 transition-colors focus:outline-none z-10"
        >
            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
            </svg>
        </button>
    </div>
    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-sm text-rose-400" />
</div>

            <div class="pt-2">
                <x-primary-button class="w-full neon-pulse">
                    {{ __('Register') }}
                </x-primary-button>
            </div>
        </form>

        <p class="text-center text-sm text-slate-400 pt-2">
            Already registered? <a href="{{ route('login') }}" class="text-violet-400 hover:text-white font-semibold">Sign in</a>
        </p>
    </div>
</x-guest-layout>
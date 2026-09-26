<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Profile Settings') }}
            </h2>
            <a href="javascript:history.back()" class="btn btn-outline btn-small" title="Go back">
                <span>←</span>
                <span>Back</span>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Profile Information Section -->
            <div class="p-4 sm:p-8 bg-white/5 border border-white/10 shadow sm:rounded-lg profile-section">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Change Password Section -->
            <div class="p-4 sm:p-8 bg-white/5 border border-white/10 shadow sm:rounded-lg profile-section">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account Section -->
            <div class="p-4 sm:p-8 bg-white/5 border border-white/10 shadow sm:rounded-lg profile-section profile-danger">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

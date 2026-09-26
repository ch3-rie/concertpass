<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Concert Tickets') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @include('layouts.partials.tailwind-cdn-assets')
    </head>
    <body class="min-h-screen bg-[#050507] text-white antialiased">
        <div class="min-h-screen grid lg:grid-cols-[1.1fr_0.9fr]">
            <div class="relative hidden lg:flex items-center justify-center overflow-hidden bg-[#0D0D18] p-10">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(124,58,237,0.25),transparent_40%)]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_right,rgba(59,130,246,0.16),transparent_35%)]"></div>
                <div class="relative z-10 max-w-xl text-center">
                    <p class="text-sm uppercase tracking-[0.4em] text-violet-400/70 mb-4">Live reservation platform</p>
                    <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-white mb-6">Modern concerts. Secure seats.</h1>
                    <p class="text-base text-slate-300">Sign in or register to explore upcoming shows, select premium seats, and manage bookings with a neon-infused UI.</p>
                </div>
            </div>

            <div class="flex items-center justify-center p-6 sm:p-12">
                <div class="w-full max-w-lg glass-panel rounded-[2rem] border border-white/10 p-8 shadow-xl">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>

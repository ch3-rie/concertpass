<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php($isAdminPanel = request()->routeIs('admin.*') || (auth()->check() && auth()->user()->role === 'admin' && request()->routeIs('dashboard')))
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Concert Tickets') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @if(!$isAdminPanel)
            <style>
                html, body, * {
                    color: #ffffff !important;
                }
                body {
                    background-color: #0f0f14 !important;
                }
                .page-transition {
                    opacity: 1 !important;
                    visibility: visible !important;
                }
            </style>
        @endif

        @include('layouts.partials.tailwind-cdn-assets')
    </head>
    <body class="{{ request()->routeIs('admin.*') ? 'admin-layout' : '' }}">
        @if(request()->routeIs('admin.*'))
            {{ $slot }}
        @else
            <div class="page-transition">
                @auth
                    @if(auth()->user()->role === 'admin')
                        @include('layouts.navigation-admin')
                    @else
                        @include('layouts.navigation')
                    @endif
                @else
                    @include('layouts.navigation')
                @endauth

                @if(session('success') || session('status'))
                    <div class="alert alert-success">
                        {{ session('success') ?? session('status') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                @isset($header)
                    <header class="page-header">
                        <div class="container">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main>
                    <div class="container">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        @endif
    </body>
</html>

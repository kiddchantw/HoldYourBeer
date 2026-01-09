<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Google Analytics -->
        <x-google-analytics />

        <link rel="icon" href="{{ asset('images/favicon.ico') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans text-gray-900 antialiased bg-orange-50">
        <div class="min-h-screen flex flex-col justify-center items-center pt-0 pb-12 relative">
            <x-background />

            <!-- Logo with enhanced styling -->
            <div class="mb-4 relative z-10 transition-transform hover:scale-105 duration-300">
                <a href="/" class="block bg-white p-4 rounded-full shadow-xl ring-4 ring-orange-100/50">
                    <img src="{{ asset('images/icon_v1_removed_bg.png') }}" alt="Logo" class="w-20 h-20 drop-shadow-lg">
                </a>
            </div>

            @if (isset($heading))
                <div class="relative z-20 mb-3 text-center px-4">
                    {{ $heading }}
                </div>
            @endif

            <!-- Form container with enhanced styling -->
            <div class="w-[90%] sm:max-w-md mt-3 mb-6 px-6 py-4 bg-white/95 backdrop-blur-sm shadow-xl overflow-hidden rounded-2xl relative z-20 border border-white/30">
                <div class="flex justify-end mb-3">
                    <x-language-switcher />
                </div>
                {{ $slot }}
            </div>

            <!-- Copyright Text (Previously in Footer) -->
            <div class="relative z-20 text-center text-sm text-gray-500/80 mt-2 pb-6">
                <p>&copy; 2025-{{ date('Y') }} HoldYourBeers</p>
            </div>

        </div>

        <!-- Cookie Consent Banner -->
        <x-cookie-consent />
        
        @livewireScripts
    </body>
</html>

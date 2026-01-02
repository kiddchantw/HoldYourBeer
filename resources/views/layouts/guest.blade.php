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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative overflow-hidden">
            <x-background />

            <!-- Logo with enhanced styling -->
            <div class="relative z-20 mb-6">
                <a href="/" class="block transform hover:scale-105 transition-transform duration-300">
                    <img src="{{ asset('icon_v1_removed_bg.png') }}" alt="Logo" class="w-20 h-20 drop-shadow-lg">
                </a>
            </div>

            <!-- Form container with enhanced styling -->
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white/95 backdrop-blur-sm shadow-xl overflow-hidden sm:rounded-xl relative z-20 border border-white/30">
                <div class="flex justify-end mb-3">
                    <x-language-switcher />
                </div>
                {{ $slot }}
            </div>

        </div>

        <!-- Fixed Footer -->
        <x-footer />

        <!-- Cookie Consent Banner -->
        <x-cookie-consent />
        
        @livewireScripts
    </body>
</html>

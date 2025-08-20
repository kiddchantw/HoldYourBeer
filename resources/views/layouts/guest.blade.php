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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative overflow-hidden">
            <!-- Main background with lighter logo-inspired colors -->
            <div class="absolute inset-0 bg-gradient-to-br from-orange-100 via-amber-50 to-yellow-100"></div>
            
            <!-- Secondary gradient layer -->
            <div class="absolute inset-0 bg-gradient-to-tr from-orange-200/30 via-amber-100/40 to-yellow-200/30"></div>
            
            <!-- Floating beer bubbles effect -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <!-- Large bubbles -->
                <div class="absolute top-16 left-16 w-20 h-20 bg-amber-300/40 rounded-full blur-sm animate-pulse"></div>
                <div class="absolute top-32 right-24 w-16 h-16 bg-orange-300/45 rounded-full blur-sm animate-pulse" style="animation-delay: 1s;"></div>
                <div class="absolute bottom-32 left-24 w-24 h-24 bg-yellow-300/40 rounded-full blur-sm animate-pulse" style="animation-delay: 2s;"></div>
                <div class="absolute bottom-20 right-32 w-18 h-18 bg-amber-300/35 rounded-full blur-sm animate-pulse" style="animation-delay: 3s;"></div>
                
                <!-- Medium bubbles -->
                <div class="absolute top-48 left-8 w-12 h-12 bg-orange-300/40 rounded-full blur-sm animate-pulse" style="animation-delay: 0.5s;"></div>
                <div class="absolute top-64 right-8 w-14 h-14 bg-yellow-300/45 rounded-full blur-sm animate-pulse" style="animation-delay: 1.5s;"></div>
                <div class="absolute bottom-48 left-8 w-10 h-10 bg-amber-300/40 rounded-full blur-sm animate-pulse" style="animation-delay: 2.5s;"></div>
                
                <!-- Small bubbles -->
                <div class="absolute top-20 right-8 w-8 h-8 bg-orange-300/35 rounded-full blur-sm animate-pulse" style="animation-delay: 0.8s;"></div>
                <div class="absolute top-40 left-32 w-6 h-6 bg-yellow-300/40 rounded-full blur-sm animate-pulse" style="animation-delay: 1.2s;"></div>
                <div class="absolute bottom-16 left-16 w-7 h-7 bg-amber-300/35 rounded-full blur-sm animate-pulse" style="animation-delay: 2.8s;"></div>
                <div class="absolute bottom-40 right-16 w-5 h-5 bg-orange-300/40 rounded-full blur-sm animate-pulse" style="animation-delay: 3.2s;"></div>
            </div>
            
            <!-- Subtle pattern overlay for texture -->
            <div class="absolute inset-0 opacity-3">
                <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.2) 1px, transparent 1px), radial-gradient(circle at 75% 75%, rgba(255,255,255,0.2) 1px, transparent 1px); background-size: 80px 80px;"></div>
            </div>
            
            <!-- Warm glow effect behind the form -->
            <div class="absolute inset-0 bg-gradient-radial from-orange-200/20 via-transparent to-transparent"></div>
            
            <!-- Logo with enhanced styling -->
            <div class="relative z-20 mb-6">
                <a href="/" class="block transform hover:scale-105 transition-transform duration-300">
                    <x-application-logo class="w-20 h-20 drop-shadow-lg" />
                </a>
            </div>

            <!-- Form container with enhanced styling -->
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white/95 backdrop-blur-sm shadow-xl overflow-hidden sm:rounded-xl relative z-20 border border-white/30">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

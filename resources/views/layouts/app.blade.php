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
    <body class="font-sans antialiased bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-100">
        <div class="min-h-screen bg-white flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header id="page-header" class="bg-white shadow transition-all duration-300 ease-in-out">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center relative">
                            <div id="header-content" class="flex-1">
                                {{ $header }}
                            </div>
                            <button 
                                id="close-header-btn"
                                onclick="closeHeader()"
                                class="ml-4 text-gray-400 hover:text-gray-600 transition-colors duration-200 flex-shrink-0"
                                aria-label="Close header"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </header>
                <script>
                    // 檢查 localStorage 中是否已關閉
                    if (localStorage.getItem('headerClosed') === 'true') {
                        document.getElementById('page-header').style.display = 'none';
                    }

                    function closeHeader() {
                        const header = document.getElementById('page-header');
                        header.style.transition = 'opacity 0.3s ease-in-out, max-height 0.3s ease-in-out';
                        header.style.opacity = '0';
                        header.style.maxHeight = header.offsetHeight + 'px';
                        
                        setTimeout(() => {
                            header.style.display = 'none';
                            localStorage.setItem('headerClosed', 'true');
                        }, 300);
                    }

                    // 允許重新顯示（如果需要，可以添加一個按鈕來重新顯示）
                    function showHeader() {
                        const header = document.getElementById('page-header');
                        localStorage.removeItem('headerClosed');
                        header.style.display = '';
                        header.style.opacity = '0';
                        header.style.maxHeight = '0';
                        
                        setTimeout(() => {
                            header.style.transition = 'opacity 0.3s ease-in-out, max-height 0.3s ease-in-out';
                            header.style.opacity = '1';
                            header.style.maxHeight = '200px';
                        }, 10);
                    }
                </script>
            @endisset

            <!-- Page Content -->
            <main class="flex-1 flex flex-col">
                {{ $slot }}
            </main>
        </div>

        <!-- Cookie Consent Banner -->
        <x-cookie-consent />

        @livewireScripts
        @stack('scripts')
    </body>
</html>

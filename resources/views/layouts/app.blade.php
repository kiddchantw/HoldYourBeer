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

        <!-- Material Icons -->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

        <!-- Google Analytics -->
        <x-google-analytics />

        <link rel="icon" href="{{ asset('images/favicon.ico') }}">

        <!-- Scripts -->
        <script>
            window.onboardingTranslations = {
                steps: {
                    beer_list: {
                        title: "{{ __('啤酒收藏列表') }}",
                        description: "{!! __('這是你的啤酒收藏，會顯示你追蹤的所有啤酒') !!}"
                    },
                    add_beer: {
                        title: "{{ __('新增啤酒') }}",
                        description: "{!! __('點這裡可以新增一款啤酒到你的收藏') !!}",
                        description_empty: "{!! __('點這裡開始你的第一支啤酒追蹤！') !!}"
                    },
                    counter: {
                        title: "{{ __('計數器') }}",
                        description: "{!! __('用 +/- 按鈕記錄你喝了幾杯') !!}"
                    },
                    charts: {
                        title: "{{ __('圖表區域') }}",
                        description: "{!! __('這裡可以查看你的飲酒統計') !!}",
                        footer: "{!! __('即使現在沒有數據，未來這裡會充滿你的品飲紀錄！') !!}"
                    },
                    type_selector: {
                        title: "{{ __('圖表類型切換') }}",
                        description: "{!! __('切換不同的統計圖表') !!}:<br>• 📊 {{ __('長條圖') }}<br>• 🥧 {{ __('圓餅圖') }}<br>• 📈 {{ __('折線圖') }}"
                    },
                    time_filter: {
                        title: "{{ __('時間篩選器') }}",
                        description: "{!! __('選擇要查看的時間範圍') !!}。<br><br>📅 <strong>{{ __('按月份篩選數據') }}</strong>"
                    }
                },
                buttons: {
                    next: "{{ __('下一步') }}",
                    prev: "{{ __('上一步') }}",
                    done: "{{ __('完成') }}",
                }
            };

            window.appRoutes = {
                onboarding_complete: "{{ route('onboarding.complete', ['locale' => app()->getLocale()]) }}",
                dashboard: "{{ route('dashboard', ['locale' => app()->getLocale()]) }}",
                charts: "{{ route('charts', ['locale' => app()->getLocale()]) }}"
            };
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-100">
        <div class="min-h-screen flex flex-col">
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
            <main class="flex-1 flex flex-col {{ $withFooterPadding ? 'pb-16' : '' }}">
                {{ $slot }}
            </main>
        </div>

        <!-- Bottom Navigation Bar -->
        @include('layouts.bottom-navbar')

        <!-- Fixed Footer -->
        @unless(isset($hideFooter) && $hideFooter)
            <x-footer />
        @endunless

        <!-- Cookie Consent Banner -->
        <x-cookie-consent />

        @livewireScripts
        @stack('scripts')
    </body>
</html>

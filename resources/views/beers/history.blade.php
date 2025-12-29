<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $beer->brand->name }} {{ $beer->name }}
            </h2>
            <div class="text-gray-600 font-medium">
                {{ __('Current Count') }}: {{ $userBeerCount->count }}
            </div>
        </div>
    </x-slot>

    <style>
        /* 讓導覽列固定在頂部 */
        nav {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 60 !important;
        }

        /* 讓標題固定在導覽列下方 */
        header {
            position: fixed !important;
            top: 64px !important; /* 導覽列的高度 */
            left: 0 !important;
            right: 0 !important;
            z-index: 50 !important;
        }

        /* 調整主要內容的 margin-top 避免被導覽列和標題遮擋 */
        main {
            margin-top: 140px !important; /* 導覽列 + 標題的總高度 */
        }

        /* 強制隱藏所有滾動條 */
        * {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
        }
        *::-webkit-scrollbar-track {
            display: none !important;
        }
        *::-webkit-scrollbar-thumb {
            display: none !important;
        }
        .no-scrollbar {
            overflow-y: scroll !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none !important;
        }
    </style>

    <div class="py-12 relative min-h-screen overflow-auto">
        <x-background />
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10 h-full">
            <div class="bg-white/60 backdrop-blur-sm shadow-sm sm:rounded-lg min-h-full overflow-visible">
                <div class="min-h-full">
                <div class="p-6">
                    <!-- Card Title -->
                    <div class="mb-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('Tasting History') }}</h3>
                    </div>

                    <livewire:tasting-history :beerId="$beer->id" />
                </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

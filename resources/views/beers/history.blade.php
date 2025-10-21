<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $beer->brand->name }} {{ $beer->name }}
            </h2>
            <div class="text-gray-600 font-medium">
                Current Count: {{ $userBeerCount->count }}
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
                        <h3 class="text-lg font-semibold text-gray-800">Tasting History</h3>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mb-6 flex space-x-4">
                        <form action="{{ route('tasting.increment', ['id' => $userBeerCount->id, 'locale' => app()->getLocale()]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg border border-green-700 shadow-md" style="background-color: #16a34a !important; color: white !important; border-color: #15803d !important;">
                                + 新增
                            </button>
                        </form>

                        <form action="{{ route('tasting.decrement', ['id' => $userBeerCount->id, 'locale' => app()->getLocale()]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg border border-red-700 shadow-md" style="background-color: #dc2626 !important; color: white !important; border-color: #b91c1c !important;">
                                - 移除
                            </button>
                        </form>
                    </div>

                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($tastingLogs as $log)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                @if($log->action === 'initial')
                                                    <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </span>
                                                @elseif($log->action === 'increment')
                                                    <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                @elseif($log->action === 'decrement')
                                                    <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                @else
                                                    <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">{{ $log->action }}</p>
                                                    @if($log->note)
                                                        <p class="mt-2 text-sm text-gray-600">{{ $log->note }}</p>
                                                    @endif
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <!-- 顯示用戶本地時間 -->
                                                    <time datetime="{{ $log->tasted_at->toIso8601String() }}">
                                                        {{ $log->tasted_at->setTimezone('Asia/Taipei')->format('F j, Y, g:i a') }}
                                                    </time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

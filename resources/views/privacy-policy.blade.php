<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Privacy Policy') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('cookies.settings.title') }}</h3>
                    <p class="mb-6">{{ __('cookies.settings.description') }}</p>

                    <!-- Cookie 類別說明 -->
                    <div class="space-y-6">
                        <!-- 必要 Cookies -->
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-semibold text-base mb-2">{{ __('cookies.settings.necessary') }}</h4>
                            <p class="text-gray-600">{{ __('cookies.settings.necessary_description') }}</p>
                        </div>

                        <!-- 分析 Cookies -->
                        <div class="border-l-4 border-green-500 pl-4">
                            <h4 class="font-semibold text-base mb-2">{{ __('cookies.settings.analytics') }}</h4>
                            <p class="text-gray-600">{{ __('cookies.settings.analytics_description') }}</p>
                        </div>
                    </div>

                    @if(app()->getLocale() === 'zh-TW')
                        <!-- 繁體中文隱私政策詳細內容 -->
                        <div class="mt-8 space-y-4">
                            <h4 class="font-semibold text-base">我們如何使用 Cookies</h4>
                            <p class="text-gray-600">
                                HoldYourBeer 使用 cookies 來提供更好的使用體驗。我們使用 Google Analytics 來分析網站流量和使用者行為，
                                以幫助我們改善服務。這些分析工具會收集匿名化的資料，包括您的瀏覽器類型、訪問時間和頁面瀏覽路徑。
                            </p>

                            <h4 class="font-semibold text-base mt-6">您的選擇權</h4>
                            <p class="text-gray-600">
                                您可以隨時選擇拒絕使用分析 cookies。如果您拒絕，我們將不會收集您的使用數據，
                                但這不會影響網站的基本功能。您可以透過清除瀏覽器的 cookies 和 localStorage 來重新選擇您的偏好設定。
                            </p>

                            <h4 class="font-semibold text-base mt-6">資料保護</h4>
                            <p class="text-gray-600">
                                我們重視您的隱私，所有收集的資料都會按照 GDPR 和相關法規進行處理。
                                我們不會出售或分享您的個人資料給第三方（Google Analytics 除外）。
                            </p>
                        </div>
                    @else
                        <!-- English Privacy Policy Details -->
                        <div class="mt-8 space-y-4">
                            <h4 class="font-semibold text-base">How We Use Cookies</h4>
                            <p class="text-gray-600">
                                HoldYourBeer uses cookies to provide a better user experience. We use Google Analytics to analyze
                                site traffic and user behavior to help us improve our services. These analytics tools collect
                                anonymized data, including your browser type, visit time, and page viewing paths.
                            </p>

                            <h4 class="font-semibold text-base mt-6">Your Choices</h4>
                            <p class="text-gray-600">
                                You can choose to reject analytics cookies at any time. If you reject them, we will not collect
                                your usage data, but this will not affect the basic functionality of the website. You can reset
                                your preferences by clearing your browser's cookies and localStorage.
                            </p>

                            <h4 class="font-semibold text-base mt-6">Data Protection</h4>
                            <p class="text-gray-600">
                                We value your privacy and all collected data is processed in accordance with GDPR and related
                                regulations. We do not sell or share your personal data with third parties (except Google Analytics).
                            </p>
                        </div>
                    @endif

                    <!-- 返回按鈕 -->
                    <div class="mt-8">
                        <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale()]) }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Back to Dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

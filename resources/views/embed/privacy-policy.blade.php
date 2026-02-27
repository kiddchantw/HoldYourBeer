<x-embed-layout>
    @if(in_array(app()->getLocale(), ['zh-TW', 'zh_TW']))
        <h1 class="text-2xl font-bold mb-6">隱私政策</h1>

        <div class="space-y-4">
            <h3 class="text-lg font-semibold">Cookie 設定</h3>
            <p>您可以選擇接受或拒絕我們使用 cookies。</p>

            <div class="space-y-6 mt-4">
                <div class="border-l-4 border-blue-500 pl-4">
                    <h4 class="font-semibold text-base mb-2">必要 Cookies</h4>
                    <p class="text-gray-600">這些 cookies 是網站運作所必需的，無法停用。</p>
                </div>

                <div class="border-l-4 border-green-500 pl-4">
                    <h4 class="font-semibold text-base mb-2">分析 Cookies</h4>
                    <p class="text-gray-600">這些 cookies 幫助我們了解使用者如何使用網站，以改善使用體驗。</p>
                </div>
            </div>

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

                <h4 class="font-semibold text-base mt-6">個人資料的收集與使用</h4>
                <p class="text-gray-600">
                    我們收集的個人資料包括：姓名、電子郵件地址、以及您在應用程式中記錄的啤酒消費數據。
                    這些資料僅用於提供服務功能，不會用於其他商業用途。
                </p>

                <h4 class="font-semibold text-base mt-6">聯絡我們</h4>
                <p class="text-gray-600">
                    如果您對我們的隱私政策有任何疑問，請透過應用程式內的意見回饋功能與我們聯繫。
                </p>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-200 text-sm text-gray-500">
            <p>最後更新：2026-02-26</p>
        </div>
    @else
        <h1 class="text-2xl font-bold mb-6">Privacy Policy</h1>

        <div class="space-y-4">
            <h3 class="text-lg font-semibold">Cookie Settings</h3>
            <p>You can choose to accept or reject our use of cookies.</p>

            <div class="space-y-6 mt-4">
                <div class="border-l-4 border-blue-500 pl-4">
                    <h4 class="font-semibold text-base mb-2">Necessary Cookies</h4>
                    <p class="text-gray-600">These cookies are essential for the website to function and cannot be disabled.</p>
                </div>

                <div class="border-l-4 border-green-500 pl-4">
                    <h4 class="font-semibold text-base mb-2">Analytics Cookies</h4>
                    <p class="text-gray-600">These cookies help us understand how users interact with the site to improve user experience.</p>
                </div>
            </div>

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

                <h4 class="font-semibold text-base mt-6">Personal Data Collection and Usage</h4>
                <p class="text-gray-600">
                    The personal data we collect includes: name, email address, and beer consumption data you record
                    in the application. This data is used solely to provide service functionality and will not be used
                    for other commercial purposes.
                </p>

                <h4 class="font-semibold text-base mt-6">Contact Us</h4>
                <p class="text-gray-600">
                    If you have any questions about our privacy policy, please contact us through the feedback
                    feature in the application.
                </p>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-200 text-sm text-gray-500">
            <p>Last updated: 2026-02-26</p>
        </div>
    @endif
</x-embed-layout>

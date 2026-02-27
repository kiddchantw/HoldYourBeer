<x-embed-layout>
    <h1 class="text-2xl font-bold mb-6">{{ __('Terms of Service') }}</h1>

    @if(in_array(app()->getLocale(), ['zh-TW', 'zh_TW']))
        <div class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold mb-2">1. 服務說明</h2>
                <p class="text-gray-600">
                    HoldYourBeer 是一款啤酒消費追蹤應用程式，讓您記錄和管理您的啤酒品嚐體驗。
                    使用本服務即表示您同意遵守以下條款。
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">2. 帳號註冊</h2>
                <p class="text-gray-600">
                    您需要建立帳號才能使用本服務。您有責任維護帳號安全，並對帳號下的所有活動負責。
                    請提供準確的註冊資訊，並在資訊變更時及時更新。
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">3. 使用規範</h2>
                <p class="text-gray-600">您同意不會：</p>
                <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                    <li>以違反法律或法規的方式使用本服務</li>
                    <li>上傳含有惡意程式碼的內容</li>
                    <li>試圖未經授權存取本服務的系統或網路</li>
                    <li>干擾或破壞本服務的正常運作</li>
                </ul>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">4. 內容所有權</h2>
                <p class="text-gray-600">
                    您保留您在本服務中建立的內容的所有權。但為了提供服務，您授予我們使用、儲存和處理您的內容的權利。
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">5. 服務變更與終止</h2>
                <p class="text-gray-600">
                    我們保留隨時修改或終止本服務的權利。重大變更將提前通知用戶。
                    您可以隨時刪除帳號以終止使用本服務。
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">6. 免責聲明</h2>
                <p class="text-gray-600">
                    本服務按「現狀」提供。我們不對服務的持續可用性、準確性或可靠性做出任何保證。
                    本應用程式僅供記錄用途，不構成健康建議。請理性飲酒。
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">7. 隱私保護</h2>
                <p class="text-gray-600">
                    我們重視您的隱私。有關我們如何收集、使用和保護您的個人資料，
                    請參閱我們的<a href="/{{ app()->getLocale() }}/privacy-policy/embed" class="text-blue-600 underline">隱私政策</a>。
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">8. 條款更新</h2>
                <p class="text-gray-600">
                    我們可能會不時更新本服務條款。更新後繼續使用本服務即表示您接受新的條款。
                </p>
            </div>
        </div>
    @else
        <div class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold mb-2">1. Service Description</h2>
                <p class="text-gray-600">
                    HoldYourBeer is a beer consumption tracking application that allows you to record and manage
                    your beer tasting experiences. By using this service, you agree to comply with the following terms.
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">2. Account Registration</h2>
                <p class="text-gray-600">
                    You need to create an account to use this service. You are responsible for maintaining the security
                    of your account and are liable for all activities under your account. Please provide accurate
                    registration information and update it promptly when changes occur.
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">3. Acceptable Use</h2>
                <p class="text-gray-600">You agree not to:</p>
                <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                    <li>Use the service in violation of any laws or regulations</li>
                    <li>Upload content containing malicious code</li>
                    <li>Attempt unauthorized access to our systems or networks</li>
                    <li>Interfere with or disrupt the normal operation of the service</li>
                </ul>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">4. Content Ownership</h2>
                <p class="text-gray-600">
                    You retain ownership of the content you create in this service. However, to provide the service,
                    you grant us the right to use, store, and process your content.
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">5. Service Changes and Termination</h2>
                <p class="text-gray-600">
                    We reserve the right to modify or terminate the service at any time. Users will be notified
                    in advance of significant changes. You may delete your account at any time to stop using the service.
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">6. Disclaimer</h2>
                <p class="text-gray-600">
                    This service is provided "as is." We make no guarantees regarding the continued availability,
                    accuracy, or reliability of the service. This application is for tracking purposes only and
                    does not constitute health advice. Please drink responsibly.
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">7. Privacy</h2>
                <p class="text-gray-600">
                    We value your privacy. For information about how we collect, use, and protect your personal data,
                    please refer to our <a href="/{{ app()->getLocale() }}/privacy-policy/embed" class="text-blue-600 underline">Privacy Policy</a>.
                </p>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">8. Terms Updates</h2>
                <p class="text-gray-600">
                    We may update these terms of service from time to time. Continued use of the service after
                    updates constitutes acceptance of the new terms.
                </p>
            </div>
        </div>
    @endif

    <div class="mt-8 pt-4 border-t border-gray-200 text-sm text-gray-500">
        <p>{{ __('Last updated') }}: 2026-02-26</p>
    </div>
</x-embed-layout>

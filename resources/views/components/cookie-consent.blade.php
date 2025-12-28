{{-- Cookie Consent Banner Component --}}
@if(!session()->has('cookie_consent'))
<div id="cookie-consent-banner" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 shadow-lg z-50 transition-transform duration-300">
    <div class="container mx-auto max-w-7xl flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex-1 text-sm">
            <p class="mb-2 sm:mb-0">
                {{ __('cookies.banner.message') }}
            </p>
        </div>
        <div class="flex gap-3 flex-shrink-0">
            <button
                onclick="acceptCookies()"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200"
            >
                {{ __('cookies.banner.accept') }}
            </button>
            <button
                onclick="rejectCookies()"
                class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors duration-200"
            >
                {{ __('cookies.banner.reject') }}
            </button>
        </div>
    </div>
</div>

<script>
function acceptCookies() {
    setCookieConsent(true);
}

function rejectCookies() {
    setCookieConsent(false);
}

function setCookieConsent(consent) {
    // 儲存到 localStorage
    localStorage.setItem('cookie_consent', consent ? 'true' : 'false');

    // 送到後端儲存 session
    fetch('{{ route('cookie-consent.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            consent: consent
        })
    })
    .then(response => response.json())
    .then(data => {
        // 隱藏 banner
        const banner = document.getElementById('cookie-consent-banner');
        if (banner) {
            banner.style.transform = 'translateY(100%)';
            setTimeout(() => {
                banner.remove();
            }, 300);
        }

        // 如果同意，重新載入頁面以啟用 GA
        if (consent) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error saving cookie consent:', error);
    });
}

// 檢查 localStorage 是否已有選擇
document.addEventListener('DOMContentLoaded', function() {
    const localConsent = localStorage.getItem('cookie_consent');
    if (localConsent !== null) {
        // 如果 localStorage 有記錄但 session 沒有，同步到 session
        const banner = document.getElementById('cookie-consent-banner');
        if (banner) {
            setCookieConsent(localConsent === 'true');
        }
    }
});
</script>
@endif

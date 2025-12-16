@props(['type'])

@if (session($type))
    @php
        $message = session($type);
        // 檢查是否為 CSRF 相關錯誤 (通常包含 CSRF 或 page expired)
        $isCsrf = $type === 'error' && (str_contains($message, 'CSRF') || str_contains(strtolower($message), 'page expired'));
    @endphp

    @if($isCsrf)
         <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            頁面已過期，請重新整理後再試
        </div>
    @else
        <div class="{{ $type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700' }} border px-4 py-3 rounded mb-4">
            {{ $message }}
        </div>
    @endif
@endif

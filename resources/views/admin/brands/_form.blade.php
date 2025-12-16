<div class="space-y-6" x-data="{ submitting: false }">
    <!-- 品牌名稱輸入欄位 -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">
            {{ __('brands.attributes.name') }} <span class="text-red-500">*</span>
        </label>
        <input type="text"
               name="name"
               id="name"
               value="{{ old('name', $brand->name ?? '') }}"
               required
               x-bind:disabled="submitting"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror disabled:bg-gray-100 disabled:cursor-not-allowed"
               placeholder="{{ __('brands.attributes.name') }}">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- 錯誤訊息顯示 -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 提交/取消按鈕 -->
    <div class="flex items-center justify-end space-x-3">
        <a href="{{ route('admin.dashboard', ['locale' => app()->getLocale(), 'tab' => 'brands']) }}"
           x-bind:class="submitting ? 'pointer-events-none opacity-50' : ''"
           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            {{ __('brands.buttons.cancel') }}
        </a>
        <button type="submit"
                x-on:click="submitting = true"
                x-bind:disabled="submitting"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
            <span x-show="!submitting">{{ __('brands.buttons.submit') }}</span>
            <span x-show="submitting" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('處理中...') }}
            </span>
        </button>
    </div>
</div>

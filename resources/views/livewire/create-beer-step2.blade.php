{{-- 階段二：選填欄位（店家 + 筆記） --}}
<div class="space-y-6">
    {{-- 進度指示器 --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">{{ __('Step 2 of 2') }}</span>
            <span class="text-sm text-gray-500">{{ __('Additional Details (Optional)') }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 100%"></div>
        </div>
    </div>

    {{-- 購買店家欄位 --}}
    <div class="relative" @click.away="$wire.set('shop_suggestions', [])">
        <x-input-label for="shop_name" :value="__('Purchase Shop (Optional)')" />
        <input
            id="shop_name"
            type="text"
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            wire:model.live.debounce.300ms="shop_name"
            placeholder="{{ __('Enter shop name...') }}"
            wire:loading.attr="disabled"
            wire:target="save"
            autocomplete="off"
        />
        {{-- 建議列表：點擊項目時觸發 selectShop --}}
        @if(count($shop_suggestions) > 0)
            <ul class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-40 overflow-y-auto">
                @foreach($shop_suggestions as $index => $suggestion)
                    <li
                        wire:key="shop-{{ $index }}"
                        wire:click="selectShop('{{ $suggestion['name'] }}')"
                        class="px-3 py-2 hover:bg-amber-50 cursor-pointer text-sm text-gray-700 border-b border-gray-100 last:border-b-0"
                    >
                        <div class="flex items-center justify-between">
                            <span>{{ $suggestion['name'] }}</span>
                            @if(isset($suggestion['total_reports']) && $suggestion['total_reports'] > 0)
                                <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">
                                    {{ $suggestion['total_reports'] }} {{ __('reports') }}
                                </span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
        @error('shop_name') <x-input-error :messages="$errors->get('shop_name')" class="mt-2" /> @enderror
    </div>

    {{-- 品嘗筆記欄位 --}}
    <div>
        <x-input-label for="note" :value="__('Tasting Note (Optional)')" />
        <textarea 
            id="note" 
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
            wire:model="note" 
            placeholder="{{ __('How did it taste? Any memorable moments?') }}"
            rows="4"
        ></textarea>
        @error('note') <x-input-error :messages="$errors->get('note')" class="mt-2" /> @enderror
    </div>

    {{-- 按鈕組 --}}
    <div class="flex flex-col sm:flex-row items-center gap-3">
        {{-- Back 按鈕 --}}
        <button 
            type="button"
            wire:click="previousStep" 
            wire:loading.attr="disabled" 
            wire:target="save,previousStep" 
            class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors disabled:opacity-50"
        >
            ← {{ __('Back') }}
        </button>

        {{-- Save 按鈕 --}}
        <x-beer-button 
            wire:loading.attr="disabled" 
            wire:target="save" 
            class="w-full sm:flex-1"
        >
            <span wire:loading.remove wire:target="save">{{ __('Save Beer') }}</span>
            <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
        </x-beer-button>
    </div>
</div>

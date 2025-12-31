{{-- 階段一：必填欄位（品牌 + 啤酒名稱） --}}
<div class="space-y-6">
    {{-- 進度指示器 --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">{{ __('Step 1 of 2') }}</span>
            <span class="text-sm text-gray-500">{{ __('Basic Information') }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 50%"></div>
        </div>
    </div>

    {{-- 品牌欄位 --}}
    <div class="relative" @click.away="$wire.set('brand_suggestions', [])">
        <x-input-label for="brand_name" :value="__('Brand')" />
        <input
            id="brand_name"
            type="text"
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            wire:model.live.debounce.300ms="brand_name"
            placeholder="{{ __('Enter brand name...') }}"
            autofocus
            autocomplete="off"
        />
        {{-- 建議列表：點擊項目時觸發 selectBrand --}}
        @if(count($brand_suggestions) > 0)
            <ul class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-40 overflow-y-auto">
                @foreach($brand_suggestions as $index => $suggestion)
                    <li
                        wire:key="brand-{{ $index }}"
                        wire:click="selectBrand('{{ $suggestion['name'] }}')"
                        class="px-3 py-2 hover:bg-amber-50 cursor-pointer text-sm text-gray-700 border-b border-gray-100 last:border-b-0"
                    >
                        {{ $suggestion['name'] }}
                    </li>
                @endforeach
            </ul>
        @endif
        @error('brand_name') <x-input-error :messages="$errors->get('brand_name')" class="mt-2" /> @enderror
    </div>

    {{-- 啤酒名稱欄位 --}}
    <div class="relative" @click.away="$wire.set('beer_suggestions', [])">
        <x-input-label for="name" :value="__('Beer Name')" />
        <input
            id="name"
            type="text"
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            wire:model.live.debounce.300ms="name"
            placeholder="{{ __('Enter beer name...') }}"
            autocomplete="off"
        />
        {{-- 建議列表：點擊項目時觸發 selectBeer --}}
        @if(count($beer_suggestions) > 0)
            <ul class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-40 overflow-y-auto">
                @foreach($beer_suggestions as $index => $suggestion)
                    <li
                        wire:key="beer-{{ $index }}"
                        wire:click="selectBeer('{{ $suggestion['name'] }}')"
                        class="px-3 py-2 hover:bg-amber-50 cursor-pointer text-sm text-gray-700 border-b border-gray-100 last:border-b-0"
                    >
                        {{ $suggestion['name'] }}
                    </li>
                @endforeach
            </ul>
        @endif
        @error('name') <x-input-error :messages="$errors->get('name')" class="mt-2" /> @enderror
    </div>

    {{-- Next Step 按鈕 --}}
    <div class="flex items-center gap-4">
        <x-beer-button 
            type="button"
            wire:click="nextStep" 
            wire:loading.attr="disabled" 
            wire:target="nextStep" 
            class="w-full"
        >
            <span wire:loading.remove wire:target="nextStep">{{ __('Next Step') }} →</span>
            <span wire:loading wire:target="nextStep">{{ __('Validating...') }}</span>
        </x-beer-button>
    </div>
</div>

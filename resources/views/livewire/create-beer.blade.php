<div>
    <form wire:submit.prevent="save" class="space-y-6 relative">
        {{-- Loading 遮罩 --}}
        <div wire:loading.flex wire:target="save" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10 rounded-lg">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto"></div>
                <p class="mt-2 text-gray-700">{{ __('Saving beer...') }}</p>
            </div>
        </div>

        {{-- 表單內容 --}}
        <div wire:loading.class="opacity-50 pointer-events-none" wire:target="save">
            
            @if($currentStep === 1)
                {{-- 階段一：必填欄位 --}}
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
                        @if(count($brand_suggestions) > 0)
                            <ul class="absolute z-[60] w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-40 overflow-y-auto">
                                @foreach($brand_suggestions as $index => $suggestion)
                                    <li
                                        wire:key="brand-sugg-{{ $index }}"
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
                        @if(count($beer_suggestions) > 0)
                            <ul class="absolute z-[60] w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-40 overflow-y-auto">
                                @foreach($beer_suggestions as $index => $suggestion)
                                    <li
                                        wire:key="beer-sugg-{{ $index }}"
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

            @elseif($currentStep === 2)
                {{-- 階段二：選填欄位 --}}
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

                    {{-- 已存在啤酒提示 --}}
                    @if($existingBeerInfo)
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-amber-800">
                                        {{ __('You already have this beer!') }}
                                    </h3>
                                    <div class="mt-2 text-sm text-amber-700">
                                        <p>
                                            <strong>{{ $existingBeerInfo['brand_name'] }} - {{ $existingBeerInfo['beer_name'] }}</strong>
                                        </p>
                                        <p class="mt-1">
                                            {{ __('Current count') }}: <span class="font-semibold">{{ $existingBeerInfo['count'] }}</span>
                                            @if($existingBeerInfo['last_tasted_at'])
                                                <span class="text-amber-600 ml-2">({{ __('Last tasted') }}: {{ $existingBeerInfo['last_tasted_at'] }})</span>
                                            @endif
                                        </p>
                                        <p class="mt-2 text-xs text-amber-600">
                                            {{ __('Saving will add to your existing count.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

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
                        @if(count($shop_suggestions) > 0)
                            <ul class="absolute z-[60] w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-40 overflow-y-auto">
                                @foreach($shop_suggestions as $index => $suggestion)
                                    <li
                                        wire:key="shop-sugg-{{ $index }}"
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
                            wire:key="note_input"
                            id="note" 
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                            wire:model="note" 
                            placeholder="{{ __('How did it taste? Any memorable moments?') }}"
                            rows="4"
                        ></textarea>
                        @error('note') <x-input-error :messages="$errors->get('note')" class="mt-2" /> @enderror
                    </div>

                    {{-- 數量欄位 --}}
                    <div>
                        <div class="flex items-center justify-center mt-4 mb-2">
                             <x-input-label for="quantity" :value="__('Quantity')" class="sr-only" />
                        </div>
                        <div class="flex items-center justify-center gap-8">
                            {{-- Minus Button --}}
                            <button 
                                type="button"
                                wire:click="decreaseQuantity" 
                                class="w-12 h-12 rounded-full bg-white border border-gray-300 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                @disabled($quantity <= 1)
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"></path>
                                </svg>
                            </button>

                            {{-- Number Display --}}
                            <span class="text-4xl font-bold text-gray-800 w-16 text-center select-none tracking-tight font-mono">
                                {{ $quantity }}
                            </span>

                            {{-- Plus Button --}}
                            <button 
                                type="button"
                                wire:click="increaseQuantity" 
                                class="w-12 h-12 rounded-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 flex items-center justify-center text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all shadow-md active:transform active:scale-95"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                        @error('quantity') <x-input-error :messages="$errors->get('quantity')" class="mt-2 text-center" /> @enderror
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
            @endif
        </div>
    </form>
</div>

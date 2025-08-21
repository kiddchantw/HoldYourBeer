<div>
    <form wire:submit.prevent="save" class="space-y-6 relative">
    <div wire:loading.flex wire:target="save" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10 rounded-lg">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto"></div>
            <p class="mt-2 text-gray-700">{{ __('Saving beer...') }}</p>
        </div>
    </div>

    <div wire:loading.class="opacity-50 pointer-events-none" wire:target="save">
        <div>
            <x-input-label for="brand_name" :value="__('Brand')" />
            <x-text-input id="brand_name" type="text" class="mt-1 block w-full" wire:model.debounce.300ms="brand_name" placeholder="Enter brand name..." wire:loading.attr="disabled" wire:target="save" />
            @if(count($brand_suggestions) > 0)
                <ul class="mt-2 bg-gray-50 border border-gray-200 rounded-md shadow-sm max-h-40 overflow-y-auto">
                    @foreach($brand_suggestions as $suggestion)
                        <li wire:click="selectBrand('{{ $suggestion['name'] }}')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm text-gray-700 border-b border-gray-100 last:border-b-0">
                            {{ $suggestion['name'] }}
                        </li>
                    @endforeach
                </ul>
            @endif
            @error('brand_name') <x-input-error :messages="$errors->get('brand_name')" class="mt-2" /> @enderror
        </div>

        <div>
            <x-input-label for="name" :value="__('Beer Name')" />
            <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model.debounce.300ms="name" placeholder="Enter beer name..." wire:loading.attr="disabled" wire:target="save" />
            @if(count($beer_suggestions) > 0)
                <ul class="mt-2 bg-gray-50 border border-gray-200 rounded-md shadow-sm max-h-40 overflow-y-auto">
                    @foreach($beer_suggestions as $suggestion)
                        <li wire:click="selectBeer('{{ $suggestion['name'] }}')" class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm text-gray-700 border-b border-gray-100 last:border-b-0">
                            {{ $suggestion['name'] }}
                        </li>
                    @endforeach
                </ul>
            @endif
            @error('name') <x-input-error :messages="$errors->get('name')" class="mt-2" /> @enderror
        </div>

        <div>
            <x-input-label for="style" :value="__('Style')" />
            <x-text-input id="style" type="text" class="mt-1 block w-full" wire:model="style" placeholder="Enter beer style (Optional)..." wire:loading.attr="disabled" wire:target="save" />
            @error('style') <x-input-error :messages="$errors->get('style')" class="mt-2" /> @enderror
        </div>

        <div>
            <x-input-label for="note" :value="__('Tasting Note (Optional)')" />
            <textarea id="note" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="note" placeholder="Enter a tasting note..."></textarea>
            @error('note') <x-input-error :messages="$errors->get('note')" class="mt-2" /> @enderror
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ __('Save Beer') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
            </x-primary-button>
        </div>
    </div>
</form>
</div>
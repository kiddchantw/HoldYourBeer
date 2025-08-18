<div>
    <form wire:submit.prevent="save">
        <div>
            <label for="brand_name">Brand</label>
            <input type="text" id="brand_name" wire:model.debounce.300ms="brand_name">
            @if(count($brand_suggestions) > 0)
                <ul>
                    @foreach($brand_suggestions as $suggestion)
                        <li wire:click="selectBrand('{{ $suggestion['name'] }}')">{{ $suggestion['name'] }}</li>
                    @endforeach
                </ul>
            @endif
            @error('brand_name') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="name">Beer Name</label>
            <input type="text" id="name" wire:model.debounce.300ms="name">
            @if(count($beer_suggestions) > 0)
                <ul>
                    @foreach($beer_suggestions as $suggestion)
                        <li wire:click="selectBeer('{{ $suggestion['name'] }}')">{{ $suggestion['name'] }}</li>
                    @endforeach
                </ul>
            @endif
            @error('name') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="style">Style</label>
            <input type="text" id="style" wire:model="style">
            @error('style') <span>{{ $message }}</span> @enderror
        </div>

        <button type="submit">Save</button>
    </form>
</div>
<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Brand Details') }}
            </h2>
            <a href="{{ route('admin.brands.index', ['locale' => app()->getLocale()]) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('Name') }}
                        </label>
                        <div class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight bg-gray-100">
                            {{ $brand->name }}
                        </div>
                    </div>

                    <div>
                        <h3 class="font-semibold text-lg text-gray-800 leading-tight mb-4">
                            {{ __('Associated Beers') }}
                        </h3>
                        @if($brand->beers->count() > 0)
                            <!-- This will now be handled by Livewire component but for initial view passing brand is key. 
                                 Wait, the user wants the create button too which is inside the component.
                                 So I should replace the entire block or put the component there. -->
                        @endif
                        <livewire:admin.brand-beer-manager :brand="$brand" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Brand Details') }}
            </h2>
            <div class="flex gap-3">
                <a href="{{ route('admin.brands.index', ['locale' => app()->getLocale()]) }}"
                   class="flex-1 sm:flex-none bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center touch-manipulation min-h-[44px] flex items-center justify-center">
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="p-3 sm:p-6 lg:p-12">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 text-gray-900">
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('Name') }}
                        </label>
                        <div class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight bg-gray-100">
                            {{ $brand->name }}
                        </div>
                    </div>

                    <!-- Delete Button Section -->
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <h3 class="font-semibold text-lg text-gray-800 mb-3">
                            {{ __('Danger Zone') }}
                        </h3>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-sm text-gray-700 mb-3">
                                {{ __('Once you delete this brand, all of its data will be permanently deleted.') }}
                            </p>
                            <livewire:admin.brand-delete :brand="$brand" />
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

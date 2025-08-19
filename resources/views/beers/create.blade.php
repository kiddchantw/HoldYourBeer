<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add a New Beer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-2xl">
                    <section class="space-y-6">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Beer Information') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Enter the details of the beer you want to add to your collection.') }}
                            </p>
                        </header>
                        
                        @livewire('create-beer')
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
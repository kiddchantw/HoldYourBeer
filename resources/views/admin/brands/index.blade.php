<x-admin-layout>
    <x-slot name="header">
        {{ __('brands.titles.index') }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:admin.brand-manager />
        </div>
    </div>
</x-admin-layout>

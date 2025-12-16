<x-app-layout>
    <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('brands.titles.edit') }}
            </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.brands.update', ['locale' => app()->getLocale(), 'brand' => $brand]) }}">
                        @csrf
                        @method('PUT')
                        @include('admin.brands._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

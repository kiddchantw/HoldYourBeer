{{-- Placeholder for News View --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            News
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Left Column: Recently Added Beers -->
                <div class="w-full md:w-1/2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800">
                            Recently Added Beers
                        </h3>

                        @if($recentBeers->isEmpty())
                            <p class="text-gray-500">
                                No beers have been added yet.
                            </p>
                        @else
                            <ul class="list-disc list-inside space-y-3 text-gray-700 px-2">
                                @foreach($recentBeers as $beer)
                                    <li>
                                        <span class="font-semibold">{{ $beer->brand ? $beer->brand->name : 'Unknown Brand' }}</span>
                                        -
                                        {{ $beer->name }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <!-- Right Column: System Updates (Placeholder) -->
                <div class="w-full md:w-1/2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800">
                            System Updates
                        </h3>

                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500">
                                System updates coming soon...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-600 text-lg">
                    Welcome, {{ Auth::user()->name }}
                    @if(Auth::user()->role === 'admin')
                        <span class="text-sm text-gray-500">
                            You are an administrator. <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-900">Go to Admin Dashboard</a>
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-12 relative overflow-hidden">
        <x-background />
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10">

            <!-- Beer Collection Section -->
            <!-- <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"> -->
            <div class="bg-white/60 backdrop-blur-sm overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6">
                    @if($trackedBeers->isEmpty())
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <div class="max-w-md mx-auto">
                                <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-gray-100 mb-4">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0h2M9 5a2 2 0 012 2v6a2 2 0 01-2 2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10.1M9 16.1L9 5"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Start Your Beer Collection</h3>
                                <p class="text-sm text-gray-500 mb-6">Begin tracking your favorite beers and discover new ones!</p>
                                <a href="{{ route('beers.create') }}" 
                                   class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Track my first beer
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- Beer List -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">My Beer Collection</h3>
                                <span class="text-sm text-gray-500">{{ $trackedBeers->count() }} {{ $trackedBeers->count() == 1 ? 'beer' : 'beers' }} tracked</span>
                            </div>
                        </div>

                        <!-- Beer Cards - Mobile First -->
                        <div class="space-y-4 md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-6 md:space-y-0">
                            @foreach($trackedBeers as $beerCount)
                                <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition duration-150 ease-in-out">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-lg font-medium text-gray-900 truncate">
                                                {{ $beerCount->beer->brand->name }} {{ $beerCount->beer->name }}
                                            </h4>
                                            <p class="text-sm text-gray-600 mt-1">
                                                @if($beerCount->beer->style)
                                                    {{ $beerCount->beer->style }} • 
                                                @endif
                                                <span class="font-medium">{{ $beerCount->count }} {{ $beerCount->count == 1 ? 'time' : 'times' }}</span>
                                            </p>
                                            @if($beerCount->last_tasted_at)
                                                <p class="text-xs text-gray-400 mt-2">
                                                    Last tasted: {{ $beerCount->last_tasted_at->format('M j, Y') }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="flex-shrink-0 ml-4">
                                            <form action="{{ route('tasting.decrement', $beerCount) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-full hover:bg-red-200">-</button>
                                            </form>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $beerCount->count }}
                                            </span>
                                            <form action="{{ route('tasting.increment', $beerCount) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 text-xs font-medium text-green-600 bg-green-100 rounded-full hover:bg-green-200">+</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Add More Beer Button -->
                        <div class="mt-8 text-center">
                            <a href="{{ route('beers.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add another beer
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

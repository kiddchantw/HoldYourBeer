<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-gray-600 text-lg">
                    {{ __('Welcome') }}, {{ Auth::user()->name }}
                    @if(Auth::user()->role === 'admin')
                        <span class="text-sm text-gray-500">
                            {{ __('You are an administrator.') }} <a href="{{ route('admin.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Go to Admin Dashboard') }}</a>
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
                                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Start Your Beer Collection') }}</h3>
                                <p class="text-sm text-gray-500 mb-6">{{ __('Begin tracking your favorite beers and discover new ones!') }}</p>
                                <a href="{{ route('beers.create', ['locale' => app()->getLocale() ?: 'en']) }}"
                                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ __('Track my first beer') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- Beer List -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('My Beer Collection') }}</h3>
                                <span class="text-sm text-gray-500">{{ $trackedBeers->count() }} {{ $trackedBeers->count() == 1 ? __('beer') : __('beers') }} {{ __('tracked') }}</span>
                            </div>
                        </div>

                        <!-- Beer Cards - Mobile First -->
                        <div class="space-y-4 md:grid md:grid-cols-1 lg:grid-cols-2 md:gap-6 md:space-y-0">
                            @foreach($trackedBeers as $beerCount)
                                <div class="bg-gray-50 rounded-lg overflow-hidden hover:bg-gray-100 transition duration-150 ease-in-out grid grid-cols-2 min-h-[110px]">
                                    <!-- 左側：啤酒資訊 -->
                                    <div class="p-4 flex items-center justify-center">
                                        <a href="{{ route('beers.history', ['beerId' => $beerCount->beer->id, 'locale' => app()->getLocale() ?: 'en']) }}" class="block">
                                            <div class="flex justify-between items-start">
                                                <div class="min-w-0 flex-1">
                                                    <h4 class="text-lg font-medium text-gray-900 truncate">
                                                        {{ $beerCount->beer->brand->name }} {{ $beerCount->beer->name }}
                                                    </h4>
                                                    <p class="text-sm text-gray-600 mt-1">
                                                        @if($beerCount->beer->style)
                                                            {{ $beerCount->beer->style }} •
                                                        @endif
                                                        <span class="font-medium">{{ $beerCount->count }} {{ $beerCount->count == 1 ? __('time') : __('times') }}</span>
                                                    </p>
                                                    @if($beerCount->last_tasted_at)
                                                        <p class="text-xs text-gray-400 mt-2">
                                                            {{ __('Last tasted') }}: {{ $beerCount->last_tasted_at->format('M j, Y') }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- 右側：計數器控制 -->
                                    <div class="p-4 flex items-center justify-center">
                                            <form action="{{ route('tasting.decrement', ['id' => $beerCount->id, 'locale' => app()->getLocale() ?: 'en']) }}" method="POST" class="flex-1 h-full">
                                                @csrf
                                                <button type="submit" class="w-full h-full text-3xl font-semibold text-red-700 bg-red-100 hover:bg-red-200 rounded-md">-</button>
                                            </form>
                                            <div class="flex-1 h-full flex items-center justify-center">
                                                <span class="flex items-center justify-center w-full h-full rounded-md text-2xl font-bold bg-blue-100 text-blue-800 select-none">
                                                    <span class="flex items-center justify-center w-full h-full rounded-md text-2xl font-bold bg-blue-100 text-blue-800 select-none">
                                                        {{ $beerCount->count }}
                                                    </span>
                                                </span>
                                            </div>
                                            <form action="{{ route('tasting.increment', ['id' => $beerCount->id, 'locale' => app()->getLocale() ?: 'en']) }}" method="POST" class="flex-1 h-full">
                                                @csrf
                                                <button type="submit" class="w-full h-full text-3xl font-semibold text-green-700 bg-green-100 hover:bg-green-200 rounded-md">+</button>
                                            </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Add More Beer Button -->
                        <div class="mt-8 text-center">
                            <a href="{{ route('beers.create', ['locale' => app()->getLocale() ?: 'en']) }}"
                               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                {{ __('Add another beer') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

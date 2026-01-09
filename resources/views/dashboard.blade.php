<x-app-layout :with-footer-padding="false" :hide-footer="true">
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

    <div class="pt-4 pb-20 relative overflow-hidden flex-1">
        <x-background />
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10 mt-2">

            <!-- Beer Collection Section -->
            <!-- <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg"> -->
            <div class="bg-white/60 backdrop-blur-sm overflow-hidden shadow-sm sm:rounded-lg" id="beer-list">

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
                                <!-- Mobile Button (Bottom Sheet) -->
                                <button type="button" 
                                        @click="$dispatch('open-add-beer')"
                                        class="sm:hidden inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ __('Track my first beer') }}
                                </button>

                                <!-- Desktop Button (Page Navigation) -->
                                <a href="{{ route('beers.create', ['locale' => app()->getLocale() ?: 'en']) }}"
                                   id="add-beer-button"
                                   class="hidden sm:inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200">
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
                                <!-- Add Button (Desktop Only) -->
                                <a href="{{ route('beers.create', ['locale' => app()->getLocale() ?: 'en']) }}"
                                   id="add-beer-button"
                                   class="hidden sm:inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>{{ __('Add another beer') }}</span>
                                </a>
                            </div>
                        </div>

                        <!-- Beer Cards - Mobile First -->
                        <div class="space-y-4 md:grid md:grid-cols-1 lg:grid-cols-2 md:gap-6 md:space-y-0">
                            @foreach($trackedBeers as $beerCount)
                                <div class="bg-gray-50 rounded-lg overflow-hidden hover:bg-gray-100 transition duration-150 ease-in-out flex justify-between items-center min-h-[110px] beer-counter">
                                    <!-- 左側：啤酒資訊 -->
                                    <div class="p-4 flex-1 min-w-0 flex items-center">
                                        <a href="{{ route('beers.history', ['beerId' => $beerCount->beer->id, 'locale' => app()->getLocale() ?: 'en']) }}" class="block w-full">
                                            <div class="space-y-1 text-center">
                                                <p class="text-sm text-gray-500 font-medium">
                                                    {{ $beerCount->beer->brand->name }}
                                                </p>
                                                <h4 class="text-xl font-bold text-gray-900">
                                                    {{ $beerCount->beer->name }}
                                                </h4>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- 右側：計數器控制 -->
                                    <div class="p-4 flex-shrink-0">
                                        <div class="flex items-center justify-center space-x-3">
                                            <form action="{{ route('tasting.decrement', ['id' => $beerCount->id, 'locale' => app()->getLocale() ?: 'en']) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-12 h-12 rounded-full flex items-center justify-center bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-700 shadow-md hover:shadow-lg border border-gray-200 transition-all duration-200 transform hover:scale-105">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"></path>
                                                    </svg>
                                                </button>
                                            </form>

                                            <span class="text-xl font-bold text-gray-800 w-16 h-12 flex items-center justify-center tabular-nums">{{ $beerCount->count }}</span>

                                            <form action="{{ route('tasting.increment', ['id' => $beerCount->id, 'locale' => app()->getLocale() ?: 'en']) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-12 h-12 rounded-full flex items-center justify-center bg-gradient-to-r from-amber-500 to-amber-600 text-white hover:from-amber-600 hover:to-amber-700 shadow-md hover:shadow-xl border border-gray-200 transition-all duration-200 transform hover:scale-105">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Beer Count Summary -->
                        <div class="mt-8 text-center">
                            <span class="text-sm text-gray-500">{{ $trackedBeers->count() }} {{ $trackedBeers->count() == 1 ? __('beer') : __('beers') }} {{ __('tracked') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Onboarding Modal -->
    @livewire('onboarding-modal')

    <!-- Bottom Sheet for Add Beer -->
    <x-bottom-sheet name="add-beer" max-height="85vh">
        <div class="max-w-md mx-auto">
            <header class="text-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">
                    {{ __('Add New Beer') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Enter the details of the beer you want to add to your collection.') }}
                </p>
            </header>
            @livewire('create-beer')
        </div>
    </x-bottom-sheet>
</x-app-layout>


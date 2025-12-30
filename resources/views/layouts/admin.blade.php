<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            @if(isset($header) && $header->isNotEmpty())
                {{ $header }}
            @else
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Admin Dashboard') }}
                </h2>
            @endif
        </div>
    </x-slot>

    <div class="flex flex-1 h-full flex-col">
        <!-- Top Tab Navigation (visible for all screen sizes) -->
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <nav class="flex space-x-8 overflow-x-auto scrollbar-hide" aria-label="Tabs">
                    <a href="{{ route('admin.users.index', ['locale' => app()->getLocale()]) }}"
                       class="flex items-center gap-2 px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap {{ request()->routeIs('admin.users.*') ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('admin.users.*') ? 'text-blue-600' : 'text-gray-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        {{ __('Users') }}
                    </a>

                    <a href="{{ route('admin.brands.index', ['locale' => app()->getLocale()]) }}"
                       class="flex items-center gap-2 px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap {{ request()->routeIs('admin.brands.*') ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('admin.brands.*') ? 'text-blue-600' : 'text-gray-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Brands
                    </a>

                    <a href="{{ route('admin.feedback.index', ['locale' => app()->getLocale()]) }}"
                       class="flex items-center gap-2 px-1 py-4 text-sm font-medium border-b-2 whitespace-nowrap {{ request()->routeIs('admin.feedback.*') ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }}">
                        <svg class="h-5 w-5 {{ request()->routeIs('admin.feedback.*') ? 'text-blue-600' : 'text-gray-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        Feedback
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 bg-gray-100 p-3 sm:p-6 w-full overflow-x-hidden">
            <div class="max-w-7xl mx-auto">
                {{ $slot }}
            </div>
        </main>
    </div>
</x-app-layout>

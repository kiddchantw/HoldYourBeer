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

    <div class="flex flex-1 h-full">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex-shrink-0 block min-h-screen">
            <div class="py-4">
                <nav class="space-y-1">
                     <a href="{{ route('admin.users.index', ['locale' => app()->getLocale()]) }}"
                       class="group flex items-center px-6 py-3 text-sm font-medium border-l-4 {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 border-blue-600 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        {{ __('Users') }}
                    </a>
                    
                     <a href="{{ route('admin.brands.index', ['locale' => app()->getLocale()]) }}"
                       class="group flex items-center px-6 py-3 text-sm font-medium border-l-4 {{ request()->routeIs('admin.brands.*') ? 'bg-blue-50 border-blue-600 text-blue-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="mr-3 h-6 w-6 text-gray-400 group-hover:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Brands
                    </a>
                </nav>
            </div>
        </aside>
        
        <!-- Mobile menu dropdown or toggle could be added here for small screens, 
             but for now adhering to user request for left sidebar mostly desktop oriented -->

        <!-- Main Content -->
        <main class="flex-1 bg-gray-100 p-6">
            {{ $slot }}
        </main>
    </div>
</x-app-layout>

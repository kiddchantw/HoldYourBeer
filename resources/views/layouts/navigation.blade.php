<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 relative z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 relative">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center mr-4 sm:mr-6">
                    @if(request()->routeIs('localized.dashboard'))
                        <!-- Mobile: Open Bottom Sheet -->
                        <button type="button" @click="$dispatch('open-add-beer')" class="sm:hidden focus:outline-none focus:ring-2 focus:ring-amber-500 rounded">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                        </button>
                        <!-- Desktop: Link to Dashboard -->
                        <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}" class="hidden sm:block focus:outline-none focus:ring-2 focus:ring-amber-500 rounded">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                        </a>
                    @else
                        <!-- Other pages: Always link to Dashboard -->
                        <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}" class="focus:outline-none focus:ring-2 focus:ring-amber-500 rounded">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                        </a>
                    @endif
                </div>

                <!-- Center Brand Text (Mobile Only) - Absolutely Centered -->
                <div class="absolute left-1/2 transform -translate-x-1/2 h-16 flex items-center sm:hidden">
                    <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}" 
                       class="text-lg font-bold text-amber-600 hover:text-amber-700 transition-colors">
                        HoldYourBeers
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:ml-0 sm:flex">
                    <x-nav-link :href="route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en'])" :active="request()->routeIs('localized.dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('charts', ['locale' => app()->getLocale() ?: 'en'])" :active="request()->routeIs('charts')">
                        {{ __('Charts') }}
                    </x-nav-link>
                    <x-nav-link :href="route('profile.edit', ['locale' => app()->getLocale() ?: 'en'])" :active="request()->routeIs('profile.edit')">
                        {{ __('Profile') }}
                    </x-nav-link>
                    
                    {{-- 重新看教學按鈕：只在信箱驗證後 30 天內顯示 --}}
                    @if(Auth::user()->email_verified_at && Auth::user()->email_verified_at->addDays(30)->isFuture())
                        <x-nav-link :href="route('onboarding.restart', ['locale' => app()->getLocale() ?: 'en'])">
                            <span class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('重新看教學') }}
                            </span>
                        </x-nav-link>
                    @endif
                    
                    @if(Auth::user()->role === 'admin')
                        <x-nav-link :href="route('admin.dashboard', ['locale' => app()->getLocale() ?: 'en'])" :active="request()->routeIs('admin.*')">
                            {{ __('Admin') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-language-switcher />
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en'])" :active="request()->routeIs('localized.dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('charts', ['locale' => app()->getLocale() ?: 'en'])" :active="request()->routeIs('charts')">
                {{ __('Charts') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('profile.edit', ['locale' => app()->getLocale() ?: 'en'])" :active="request()->routeIs('profile.edit')">
                {{ __('Profile') }}
            </x-responsive-nav-link>
            @if(Auth::user()->role === 'admin')
                <x-responsive-nav-link :href="route('admin.dashboard', ['locale' => app()->getLocale() ?: 'en'])" :active="request()->routeIs('admin.*')">
                    {{ __('Admin') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                {{-- 重新看教學按鈕：只在信箱驗證後 30 天內顯示 --}}
                @if(Auth::user()->email_verified_at && Auth::user()->email_verified_at->addDays(30)->isFuture())
                    <x-responsive-nav-link :href="route('onboarding.restart', ['locale' => app()->getLocale() ?: 'en'])">
                        <span class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ __('重新看教學') }}
                        </span>
                    </x-responsive-nav-link>
                @endif
                




                <div class="mt-3 space-y-1">
                    <x-language-switcher />
                </div>
            </div>
        </div>
    </div>
</nav>

{{-- Top Navigation Bar --}}
<nav class="bg-gradient-to-r from-amber-50 via-orange-50 to-yellow-100 border-b border-amber-200/50 backdrop-blur-sm relative z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo + Tutorial (Mobile) -->
            <div class="shrink-0 flex items-center gap-2">
                <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}"
                   class="focus:outline-none focus:ring-2 focus:ring-amber-500 rounded">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                </a>
                <!-- Tutorial Button (Mobile Only) -->
                <a href="{{ route('onboarding.restart', ['locale' => app()->getLocale() ?: 'en']) }}"
                   class="md:hidden flex items-center justify-center w-9 h-9 rounded-full hover:bg-amber-50 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500"
                   aria-label="{{ __('教學') }}">
                    <span class="material-icons text-2xl text-amber-600">help_outline</span>
                </a>
            </div>

            <!-- Center Brand Text (Mobile Only) - Absolutely Centered -->
            <div class="absolute left-1/2 transform -translate-x-1/2 h-16 flex items-center md:hidden">
                <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}"
                   class="text-lg font-bold text-amber-600 hover:text-amber-700 transition-colors">
                    HoldYourBeers
                </a>
            </div>

            <!-- Desktop Navigation Links (hidden on mobile, shown on md+) -->
            <div class="hidden md:flex md:items-center md:space-x-6 md:ml-6">
                <a href="{{ route('news.index', ['locale' => app()->getLocale() ?: 'en']) }}"
                   class="px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('news.index') ? 'text-amber-600 bg-amber-50' : 'text-gray-600 hover:text-amber-600 hover:bg-amber-50' }}">
                    {{ __('News') }}
                </a>
                <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}"
                   class="px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('localized.dashboard') ? 'text-amber-600 bg-amber-50' : 'text-gray-600 hover:text-amber-600 hover:bg-amber-50' }}">
                    {{ __('我的啤酒') }}
                </a>
                <a href="{{ route('charts', ['locale' => app()->getLocale() ?: 'en']) }}"
                   class="px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('charts') ? 'text-amber-600 bg-amber-50' : 'text-gray-600 hover:text-amber-600 hover:bg-amber-50' }}">
                    {{ __('統計') }}
                </a>
                <a href="{{ route('profile.edit', ['locale' => app()->getLocale() ?: 'en']) }}"
                   class="px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('profile.edit') ? 'text-amber-600 bg-amber-50' : 'text-gray-600 hover:text-amber-600 hover:bg-amber-50' }}">
                    {{ __('個人檔案') }}
                </a>
                <a href="{{ route('onboarding.restart', ['locale' => app()->getLocale() ?: 'en']) }}"
                   class="px-3 py-2 text-sm font-medium rounded-md transition-colors text-gray-600 hover:text-amber-600 hover:bg-amber-50">
                    {{ __('教學') }}
                </a>
            </div>

            <!-- Right Side: Language Switcher -->
            <div class="flex items-center">
                <x-language-switcher />
            </div>
        </div>
    </div>
</nav>

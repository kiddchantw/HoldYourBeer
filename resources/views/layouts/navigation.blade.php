{{-- Simplified Top Navigation Bar - Logo and Brand Only --}}
<nav class="bg-white border-b border-gray-100 relative z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="shrink-0 flex items-center">
                <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}"
                   class="focus:outline-none focus:ring-2 focus:ring-amber-500 rounded">
                    <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                </a>
            </div>

            <!-- Center Brand Text (Mobile Only) - Absolutely Centered -->
            <div class="absolute left-1/2 transform -translate-x-1/2 h-16 flex items-center sm:hidden">
                <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}"
                   class="text-lg font-bold text-amber-600 hover:text-amber-700 transition-colors">
                    HoldYourBeers
                </a>
            </div>

            <!-- Desktop Brand Text -->
            <div class="hidden sm:flex sm:items-center sm:ml-4">
                <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}"
                   class="text-xl font-bold text-amber-600 hover:text-amber-700 transition-colors">
                    HoldYourBeers
                </a>
            </div>

            <!-- Language Switcher (Desktop) -->
            <div class="hidden sm:flex sm:items-center">
                <x-language-switcher />
            </div>

            <!-- Language Switcher (Mobile) -->
            <div class="flex items-center sm:hidden">
                <x-language-switcher />
            </div>
        </div>
    </div>
</nav>

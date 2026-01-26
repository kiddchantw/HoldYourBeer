{{-- Bottom Navigation Bar - Hidden on Desktop (md:hidden) --}}
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50 shadow-[0_-2px_8px_rgba(0,0,0,0.1)]"
     style="height: 64px; padding-bottom: env(safe-area-inset-bottom);"
     role="navigation"
     aria-label="{{ __('主要導覽') }}">
    <div class="h-full flex items-center justify-around px-4">
        {{-- 統計 --}}
        <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}"
           class="navbar-item flex flex-col items-center justify-center min-w-[48px] min-h-[48px] px-3 py-2 gap-1 rounded-lg transition-all duration-200 cursor-pointer
                  {{ request()->routeIs('localized.dashboard') ? 'text-[#E65100]' : 'text-[#616161]' }}
                  hover:bg-gray-50 active:bg-gray-100"
           aria-label="{{ __('統計頁面') }}"
           {{ request()->routeIs('localized.dashboard') ? 'aria-current=page' : '' }}>
            <span class="material-icons text-2xl" aria-hidden="true">bar_chart</span>
            <span class="text-[11px] font-medium">{{ __('統計') }}</span>
            @if(request()->routeIs('localized.dashboard'))
                <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-8 h-[3px] bg-[#FF6F00] rounded-t-sm"></span>
            @endif
        </a>

        {{-- 我的啤酒 --}}
        <a href="{{ route('localized.dashboard', ['locale' => app()->getLocale() ?: 'en']) }}"
           class="navbar-item flex flex-col items-center justify-center min-w-[48px] min-h-[48px] px-3 py-2 gap-1 rounded-lg transition-all duration-200 cursor-pointer
                  text-[#616161]
                  hover:bg-gray-50 active:bg-gray-100"
           aria-label="{{ __('我的啤酒') }}">
            <span class="material-icons text-2xl" aria-hidden="true">local_bar</span>
            <span class="text-[11px] font-medium">{{ __('我的啤酒') }}</span>
        </a>

        {{-- 個人檔案 --}}
        <a href="{{ route('profile.edit', ['locale' => app()->getLocale() ?: 'en']) }}"
           class="navbar-item flex flex-col items-center justify-center min-w-[48px] min-h-[48px] px-3 py-2 gap-1 rounded-lg transition-all duration-200 cursor-pointer
                  {{ request()->routeIs('profile.edit') ? 'text-[#E65100]' : 'text-[#616161]' }}
                  hover:bg-gray-50 active:bg-gray-100"
           aria-label="{{ __('個人檔案') }}"
           {{ request()->routeIs('profile.edit') ? 'aria-current=page' : '' }}>
            <span class="material-icons text-2xl" aria-hidden="true">person</span>
            <span class="text-[11px] font-medium">{{ __('個人') }}</span>
            @if(request()->routeIs('profile.edit'))
                <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-8 h-[3px] bg-[#FF6F00] rounded-t-sm"></span>
            @endif
        </a>
    </div>
</nav>

<style>
    /* Focus styles for accessibility */
    .navbar-item:focus {
        outline: 2px solid #E65100;
        outline-offset: 2px;
    }

    .navbar-item:focus:not(:focus-visible) {
        outline: none;
    }

    .navbar-item:focus-visible {
        outline: 2px solid #E65100;
        outline-offset: 2px;
    }

    /* Respect user's motion preferences */
    @media (prefers-reduced-motion: reduce) {
        .navbar-item {
            transition: none;
        }
    }

    /* iOS Safe Area support */
    @supports (padding-bottom: env(safe-area-inset-bottom)) {
        nav[aria-label*="主要導覽"] {
            padding-bottom: max(0px, env(safe-area-inset-bottom));
        }
    }
</style>

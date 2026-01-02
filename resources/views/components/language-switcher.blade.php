@php
    $supportedLocales = [
        'en' => ['name' => 'English', 'native' => 'English', 'label' => 'EN'],
        'zh_TW' => ['name' => 'Traditional Chinese', 'native' => '繁體中文', 'label' => '中文']
    ];
    
    $currentLocale = app()->getLocale(); // e.g., 'en' or 'zh_TW'
    
    // Determine target locale (toggle logic)
    $targetLocaleCode = ($currentLocale === 'en') ? 'zh_TW' : 'en';
    $targetProperties = $supportedLocales[$targetLocaleCode];
    
    // URL Generation Logic
    $currentRoute = request()->route() ? request()->route()->getName() : null;
    $routeMap = [
        'localized.dashboard' => 'localized.dashboard',
        'localized.login' => 'localized.login',
        'localized.register' => 'localized.register',
        'password.request' => 'password.request',
        'password.email' => 'password.email',
        'password.reset' => 'password.reset',
        'charts' => 'charts',
        'profile.edit' => 'profile.edit',
        'admin.dashboard' => 'admin.dashboard',
        'admin.users.index' => 'admin.users.index',
        'beers.create' => 'beers.create',
        'beers.history' => 'beers.history',
        'tasting.increment' => 'tasting.increment',
        'tasting.decrement' => 'tasting.decrement',
    ];
    
    $targetRoute = $routeMap[$currentRoute] ?? 'localized.dashboard';
    
    // Carry over parameters
    $currentParams = request()->route() ? request()->route()->parameters() : [];
    unset($currentParams['locale']);
    
    // Format for URL (zh_TW -> zh-TW)
    $urlLocale = str_replace('_', '-', $targetLocaleCode);
    $routeParams = array_merge(['locale' => $urlLocale], $currentParams);
@endphp

<div class="flex items-center ml-4">
    <a href="{{ route($targetRoute, $routeParams) }}" 
       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150"
       title="Switch to {{ $targetProperties['native'] }}">
        
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" />
            <path d="M2.4 12h19.2M12 2.4c-2.8 2.5-4.2 5.7-4.2 9.6s1.4 7.1 4.2 9.6m0-19.2c2.8 2.5 4.2 5.7 4.2 9.6s-1.4 7.1-4.2 9.6" />
        </svg>
        
        <!-- Show CURRENT locale label, but clicking switches to target -->
        <!-- Or show TARGET locale label to indicate action? -->
        <!-- Based on request: "按了就切換", usually implies showing current state or a toggle icon. -->
        <!-- Let's show the CURRENT locale code (like the screenshot), but render it as a simple link -->
        <div>{{ strtoupper(str_replace('_', '-', $currentLocale)) }}</div>
    </a>
</div>

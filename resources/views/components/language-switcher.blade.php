<div class="flex items-center ml-4">
    <div class="relative">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" />
                        <path d="M2.4 12h19.2M12 2.4c-2.8 2.5-4.2 5.7-4.2 9.6s1.4 7.1 4.2 9.6m0-19.2c2.8 2.5 4.2 5.7 4.2 9.6s-1.4 7.1-4.2 9.6" />
                    </svg>
                    <div>{{ strtoupper(app()->getLocale()) }}</div>

                    <div class="ml-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                @php
                    $supportedLocales = [
                        'en' => ['name' => 'English', 'native' => 'English'],
                        'zh-TW' => ['name' => 'Traditional Chinese', 'native' => '繁體中文']
                    ];
                    $currentRoute = request()->route() ? request()->route()->getName() : null;
                    $currentLocale = app()->getLocale();

                    // Normalize route names: our app uses the same named routes across locales
                    $routeMap = [
                        'localized.dashboard' => 'localized.dashboard',
                        'localized.login' => 'localized.login',
                        'localized.register' => 'localized.register',
                        'charts' => 'charts',
                        'profile.edit' => 'profile.edit',
                        'admin.dashboard' => 'admin.dashboard',
                        'admin.users.index' => 'admin.users.index',
                        'beers.create' => 'beers.create',
                        'beers.history' => 'beers.history',
                        'tasting.increment' => 'tasting.increment',
                        'tasting.decrement' => 'tasting.decrement',
                    ];
                @endphp

                @foreach($supportedLocales as $localeCode => $properties)
                    @if($localeCode !== $currentLocale)
                        @php
                            $targetRoute = $routeMap[$currentRoute] ?? 'localized.dashboard';
                            // Carry over existing route parameters except locale
                            $currentParams = request()->route() ? request()->route()->parameters() : [];
                            unset($currentParams['locale']);
                            $routeParams = array_merge(['locale' => $localeCode], $currentParams);
                        @endphp
                        <x-dropdown-link :href="route($targetRoute, $routeParams)">
                            {{ $properties['native'] }}
                        </x-dropdown-link>
                    @endif
                @endforeach
            </x-slot>
        </x-dropdown>
    </div>
</div>

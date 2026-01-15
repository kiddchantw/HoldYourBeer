{{-- Google Login Button Component --}}
@props(['redirect' => 'dashboard'])

@php
    // 動態判斷使用本地化或非本地化路由
    $currentRouteName = request()->route()?->getName();
    $isLocalizedRoute = $currentRouteName && str_starts_with($currentRouteName, 'localized.');
    
    $socialRoute = $isLocalizedRoute ? 'localized.social.redirect' : 'social.redirect';
    $routeParams = $isLocalizedRoute 
        ? ['locale' => app()->getLocale(), 'provider' => 'google']
        : ['provider' => 'google'];
@endphp

<a href="{{ route($socialRoute, $routeParams) }}"
   {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150']) }}>
    <img src="{{ asset('images/google_logo.svg') }}" alt="Google" class="w-5 h-5 mr-2">
    <span>{{ $slot->isEmpty() ? __('Sign in with Google') : $slot }}</span>
</a>

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Get locale from URL parameter
        $locale = $request->route('locale');

        // Debug information
        \Log::info('SetLocale middleware called', [
            'url' => $request->url(),
            'path' => $request->path(),
            'route_name' => $request->route() ? $request->route()->getName() : 'no_route',
            'locale_param' => $locale,
            'current_app_locale' => app()->getLocale(),
            'segments' => $request->segments()
        ]);

        // Validate locale and set default
        if ($locale && in_array($locale, ['en', 'zh-TW'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            \Log::info('Locale set to: ' . $locale);
        } else {
            // Try to get locale from URL segments
            $segments = $request->segments();
            if (!empty($segments) && in_array($segments[0], ['en', 'zh-TW'])) {
                $locale = $segments[0];
                App::setLocale($locale);
                Session::put('locale', $locale);
                \Log::info('Locale set from segments to: ' . $locale);
            } else {
                // Set default locale if none specified
                App::setLocale('en');
                Session::put('locale', 'en');
                \Log::info('Default locale set to: en');
            }
        }

        return $next($request);
    }
}

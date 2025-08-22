<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthLocaleRedirect
{
    /**
     * Redirect guests on localized routes to the locale-specific login page.
     * Runs before the default 'auth' middleware to control redirect URL.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            $locale = $request->route('locale') ?: app()->getLocale() ?: 'en';
            return redirect()->route('localized.login', ['locale' => $locale]);
        }
        return $next($request);
    }
}



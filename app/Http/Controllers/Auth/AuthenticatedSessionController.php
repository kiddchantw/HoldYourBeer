<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, GoogleAnalyticsService $analytics): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Track user login event
        $analytics->trackUserLogin(Auth::id(), 'email');

        // Get locale from the request path or default to 'en'
        $locale = $request->segment(1);
        if (!in_array($locale, ['en', 'zh-TW'])) {
            $locale = 'en';
        }

        return redirect()->intended(route('localized.dashboard', ['locale' => $locale]));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request, GoogleAnalyticsService $analytics): RedirectResponse
    {
        // Track user logout event (before logout)
        $analytics->trackUserLogout(Auth::id());

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Get locale from the request path or default to 'en'
        $locale = $request->segment(1);
        if (!in_array($locale, ['en', 'zh-TW'])) {
            $locale = 'en';
        }

        // Redirect to the localized login page
        return redirect()->route('localized.login', ['locale' => $locale]);
    }
}

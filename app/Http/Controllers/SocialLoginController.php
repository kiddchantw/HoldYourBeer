<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;

class SocialLoginController extends Controller
{
    /**
     * Redirect to the OAuth provider
     */
    public function redirectToProvider($locale = null, $provider = null): RedirectResponse
    {
        // If called from localized route, $locale is the locale and $provider is in second param
        // If called from non-localized route, $locale is the provider
        $actualProvider = $provider ?? $locale;

        // Use stateless mode and explicitly set redirect URL
        return Socialite::driver($actualProvider)
            ->stateless()
            ->redirect();
    }

    /**
     * Handle the OAuth provider callback
     */
    public function handleProviderCallback($locale = null, $provider = null): RedirectResponse
    {
        // If called from localized route, $locale is the locale and $provider is in second param
        // If called from non-localized route, $locale is the provider
        $actualProvider = $provider ?? $locale;
        $targetLocale = ($provider !== null) ? $locale : 'en';

        try {
            $socialUser = Socialite::driver($actualProvider)->stateless()->user();
        } catch (\Exception $e) {
            $loginRoute = ($provider !== null)
                ? route('localized.login', ['locale' => $targetLocale])
                : route('login');

            return redirect($loginRoute)->withErrors([
                'social_login' => 'Unable to login with ' . ucfirst($actualProvider) . '. Please try again.'
            ]);
        }

        // Use email as the unique identifier
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Existing user - just login
            Auth::login($user, true);
        } else {
            // Create new user
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
                'email' => $socialUser->getEmail(),
                'password' => Hash::make(Str::random(16)), // Random password for OAuth users
                'email_verified_at' => now(), // OAuth users are already verified
            ]);

            event(new Registered($user));
            Auth::login($user, true);
        }

        // Redirect to dashboard with proper locale
        return redirect()->route('localized.dashboard', ['locale' => $targetLocale]);
    }
}
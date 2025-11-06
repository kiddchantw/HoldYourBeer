<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialLoginController extends Controller
{
    public function redirectToProvider($locale = null, $provider = null)
    {
        // If called from localized route, $locale is the locale and $provider is in second param
        // If called from non-localized route, $locale is the provider
        $actualProvider = $provider ?? $locale;

        return Socialite::driver($actualProvider)->redirect();
    }

    public function handleProviderCallback($locale = null, $provider = null)
    {
        // If called from localized route, $locale is the locale and $provider is in second param
        // If called from non-localized route, $locale is the provider
        $actualProvider = $provider ?? $locale;

        try {
            $socialUser = Socialite::driver($actualProvider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['social_login' => 'Unable to login with ' . ucfirst($actualProvider) . '. Please try again.']);
        }

        $user = User::where('provider', $actualProvider)
                    ->where('provider_id', $socialUser->getId())
                    ->first();

        if (!$user) {
            // Check if a user with the same email already exists
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Link social account to existing user
                $user->provider = $actualProvider;
                $user->provider_id = $socialUser->getId();
                if ($actualProvider === 'google') {
                    $user->google_id = $socialUser->getId();
                } elseif ($actualProvider === 'apple') {
                    $user->apple_id = $socialUser->getId();
                }
                $user->save();
            } else {
                // Create a new user
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(24)), // Generate a random password
                    'provider' => $actualProvider,
                    'provider_id' => $socialUser->getId(),
                    'google_id' => ($actualProvider === 'google') ? $socialUser->getId() : null,
                    'apple_id' => ($actualProvider === 'apple') ? $socialUser->getId() : null,
                ]);
            }
        }

        Auth::login($user, true);

        return redirect('/dashboard');
    }
}
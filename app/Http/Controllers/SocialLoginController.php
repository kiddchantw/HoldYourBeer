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
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['social_login' => 'Unable to login with ' . ucfirst($provider) . '. Please try again.']);
        }

        $user = User::where('provider', $provider)
                    ->where('provider_id', $socialUser->getId())
                    ->first();

        if (!$user) {
            // Check if a user with the same email already exists
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Link social account to existing user
                $user->provider = $provider;
                $user->provider_id = $socialUser->getId();
                if ($provider === 'google') {
                    $user->google_id = $socialUser->getId();
                } elseif ($provider === 'apple') {
                    $user->apple_id = $socialUser->getId();
                }
                $user->save();
            } else {
                // Create a new user
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(24)), // Generate a random password
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'google_id' => ($provider === 'google') ? $socialUser->getId() : null,
                    'apple_id' => ($provider === 'apple') ? $socialUser->getId() : null,
                ]);
            }
        }

        Auth::login($user, true);

        return redirect('/dashboard');
    }
}
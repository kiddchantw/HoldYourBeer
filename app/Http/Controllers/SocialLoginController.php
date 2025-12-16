<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserOAuthProvider;
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
            // 🔒 Security check: Prevent unverified local accounts from being hijacked
            if ($user->isLocalUser() && !$user->email_verified_at) {
                $loginRoute = ($provider !== null)
                    ? route('localized.login', ['locale' => $targetLocale])
                    : route('login');

                return redirect($loginRoute)->withErrors([
                    'social_login' => '此 email 已註冊但尚未驗證。請先完成 email 驗證，或使用密碼登入。'
                ]);
            }

            // Existing user - update verification status and OAuth link
            // OAuth providers (like Google) have already verified the email
            if (!$user->email_verified_at) {
                $user->update(['email_verified_at' => now()]);
            }

            // 📊 Record OAuth link using the new relationship table
            $user->oauthProviders()->updateOrCreate(
                [
                    'provider' => $actualProvider,
                    'provider_id' => $socialUser->getId(),
                ],
                [
                    'provider_email' => $socialUser->getEmail(),
                    'last_used_at' => now(),
                    'linked_at' => now(),
                ]
            );

            Auth::login($user, true);
        } else {
            // Create new user with OAuth provider info
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? $socialUser->getEmail(),
                'email' => $socialUser->getEmail(),
                'password' => Hash::make(Str::random(16)), // Random password for OAuth users
                'email_verified_at' => now(), // OAuth users are already verified
            ]);

            // Create OAuth provider link
            UserOAuthProvider::create([
                'user_id' => $user->id,
                'provider' => $actualProvider,
                'provider_id' => $socialUser->getId(),
                'provider_email' => $socialUser->getEmail(),
                'linked_at' => now(),
                'last_used_at' => now(),
            ]);

            event(new Registered($user));
            Auth::login($user, true);
        }

        // Redirect to dashboard with proper locale
        return redirect()->route('localized.dashboard', ['locale' => $targetLocale]);
    }

    /**
     * Link a new OAuth provider to the authenticated user's account
     */
    public function linkProvider($locale = null, $provider = null): RedirectResponse
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            $loginRoute = ($provider !== null)
                ? route('localized.login', ['locale' => $locale])
                : route('login');

            return redirect($loginRoute)->withErrors([
                'oauth_link' => '請先登入後再連結 OAuth 帳號。'
            ]);
        }

        $actualProvider = $provider ?? $locale;
        $targetLocale = ($provider !== null) ? $locale : 'en';

        try {
            $socialUser = Socialite::driver($actualProvider)->stateless()->user();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'oauth_link' => '無法連結 ' . ucfirst($actualProvider) . ' 帳號。請稍後再試。'
            ]);
        }

        $user = Auth::user();

        // 🔒 Security check: Verify OAuth email matches current user's email
        if (strtolower($socialUser->getEmail()) !== strtolower($user->email)) {
            return redirect()->back()->withErrors([
                'oauth_link' => 'OAuth 帳號的 email 與您的帳號不符，無法連結。'
            ]);
        }

        // Check if this OAuth provider is already linked to another user
        $existingLink = UserOAuthProvider::where('provider', $actualProvider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existingLink && $existingLink->user_id !== $user->id) {
            return redirect()->back()->withErrors([
                'oauth_link' => '此 OAuth 帳號已連結到其他用戶。'
            ]);
        }

        // Create or update OAuth link
        $user->oauthProviders()->updateOrCreate(
            [
                'provider' => $actualProvider,
                'provider_id' => $socialUser->getId(),
            ],
            [
                'provider_email' => $socialUser->getEmail(),
                'last_used_at' => now(),
                'linked_at' => now(),
            ]
        );

        return redirect()->back()->with('success', ucfirst($actualProvider) . ' 帳號已成功連結！');
    }

    /**
     * Unlink an OAuth provider from the authenticated user's account
     */
    public function unlinkProvider($provider): RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->withErrors([
                'oauth_unlink' => '請先登入。'
            ]);
        }

        // 🔒 Security check: Ensure user has at least one other authentication method
        if (!$user->canUnlinkOAuthProvider()) {
            return redirect()->back()->withErrors([
                'oauth_unlink' => '無法解除連結：您必須至少保留一種登入方式。'
            ]);
        }

        // Find and delete the OAuth provider link
        $deleted = $user->oauthProviders()
            ->where('provider', $provider)
            ->delete();

        if ($deleted) {
            return redirect()->back()->with('success', ucfirst($provider) . ' 帳號已成功解除連結。');
        }

        return redirect()->back()->withErrors([
            'oauth_unlink' => '找不到要解除連結的 ' . ucfirst($provider) . ' 帳號。'
        ]);
    }
}
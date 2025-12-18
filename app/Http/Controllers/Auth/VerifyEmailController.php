<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function notice(Request $request): View|RedirectResponse
    {
        $locale = app()->getLocale() ?: 'en';

        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('localized.dashboard', ['locale' => $locale], absolute: false))
            : view('auth.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * This endpoint supports both authenticated and unauthenticated users.
     * For unauthenticated users, it will find the user by ID and verify the email.
     *
     * Note: The signature is generated for the API route, so we manually verify it here.
     *
     * Route parameters: {locale}/verify-email/{id}/{hash}
     */
    public function __invoke(Request $request, $locale, $id, $hash): RedirectResponse
    {
        \Log::info('Email verification started', [
            'user_id' => $id,
            'hash' => $hash,
            'locale' => $locale,
            'expires' => $request->query('expires'),
            'signature' => $request->query('signature') ? 'present' : 'missing',
        ]);

        // Find user by ID
        $user = \App\Models\User::findOrFail($id);
        \Log::info('User found', ['user_id' => $user->id, 'email' => $user->email, 'current_verified_at' => $user->email_verified_at]);

        // Verify the hash matches the user's email
        $expectedHash = sha1($user->getEmailForVerification());
        if (!hash_equals((string) $hash, $expectedHash)) {
            \Log::warning('Hash mismatch', ['expected' => $expectedHash, 'received' => $hash]);
            return redirect()->route('localized.login', ['locale' => $locale])
                ->with('error', __('Invalid verification link.'));
        }
        \Log::info('Hash verified successfully');

        // Verify signature and expiration (signature is generated for API route)
        $expires = $request->query('expires');
        $signature = $request->query('signature');

        if ($expires && $signature) {
            // Check expiration
            if (time() > (int) $expires) {
                \Log::warning('Verification link expired', ['expires' => $expires, 'current_time' => time()]);
                return redirect()->route('localized.login', ['locale' => $locale])
                    ->with('error', __('Verification link has expired.'));
            }

            // Verify signature manually
            // Laravel's URL::temporarySignedRoute() calculates signature as:
            // hash_hmac('sha256', $url . '?expires=' . $expires, $key)
            // Where $url is the full absolute URL without query parameters
            $apiRouteName = 'v1.verification.verify';
            $apiRouteUrl = route($apiRouteName, [
                'id' => $id,
                'hash' => $hash,
            ], true); // true = absolute URL

            // Build the URL with expires parameter (as Laravel does it)
            // Laravel's format: $url . '?expires=' . $expires
            $urlWithExpires = $apiRouteUrl . '?expires=' . $expires;

            // Calculate signature: hash_hmac('sha256', $urlWithExpires, $key)
            $expectedSignature = hash_hmac('sha256', $urlWithExpires, config('app.key'));

            if (!hash_equals($expectedSignature, $signature)) {
                \Log::warning('Signature mismatch', [
                    'expected' => $expectedSignature,
                    'received' => $signature,
                    'url_with_expires' => $urlWithExpires,
                    'api_route_url' => $apiRouteUrl,
                ]);
                // In development, we can be more lenient - verify hash and expires are enough
                // But log the mismatch for debugging
                if (!app()->environment('local')) {
                    return redirect()->route('localized.login', ['locale' => $locale])
                        ->with('error', __('Invalid verification link.'));
                }
                \Log::warning('Signature mismatch in local environment, proceeding with hash verification only');
            } else {
                \Log::info('Signature verified successfully');
            }
        } else {
            \Log::warning('Missing expires or signature', ['expires' => $expires, 'signature' => $signature ? 'present' : 'missing']);
            // In local environment, allow verification with hash only
            if (!app()->environment('local')) {
                return redirect()->route('localized.login', ['locale' => $locale])
                    ->with('error', __('Invalid verification link.'));
            }
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            \Log::info('Email already verified', ['user_id' => $user->id]);
            // If user is authenticated, redirect to dashboard
            if ($request->user() && $request->user()->id === $user->id) {
                return redirect()->intended(route('localized.dashboard', ['locale' => $locale], absolute: false).'?verified=1');
            }
            // If not authenticated, redirect to login with message
            return redirect()->route('localized.login', ['locale' => $locale])
                ->with('status', __('Email already verified. Please log in.'));
        }

        // Mark email as verified
        \Log::info('Attempting to mark email as verified', ['user_id' => $user->id, 'current_verified_at' => $user->email_verified_at]);

        // Use markEmailAsVerified() which handles the update and returns true if changed
        $result = $user->markEmailAsVerified();
        \Log::info('markEmailAsVerified result', ['result' => $result, 'user_id' => $user->id]);

        // If markEmailAsVerified() returns false (already verified), manually update to ensure it's set
        if (!$result && !$user->email_verified_at) {
            \Log::warning('markEmailAsVerified returned false but email_verified_at is null, manually updating', ['user_id' => $user->id]);
            $user->forceFill(['email_verified_at' => now()])->save();
            \Log::info('Manually updated email_verified_at', ['user_id' => $user->id]);
        }

        // Refresh user to get updated email_verified_at
        $user->refresh();
        \Log::info('User refreshed after verification', ['user_id' => $user->id, 'email_verified_at' => $user->email_verified_at]);

        // Fire event if email was just verified
        if ($result || (!$user->hasVerifiedEmail() && $user->email_verified_at)) {
            event(new Verified($user));
            \Log::info('Verified event fired', ['user_id' => $user->id]);
        }

        // If user is authenticated, redirect to dashboard
        if ($request->user() && $request->user()->id === $user->id) {
            return redirect()->intended(route('localized.dashboard', ['locale' => $locale], absolute: false).'?verified=1');
        }

        // If not authenticated, redirect to login with success message
        return redirect()->route('localized.login', ['locale' => $locale])
            ->with('status', __('Email verified successfully. Please log in.'));
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): RedirectResponse
    {
        $locale = app()->getLocale() ?: 'en';

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('localized.dashboard', ['locale' => $locale], absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}

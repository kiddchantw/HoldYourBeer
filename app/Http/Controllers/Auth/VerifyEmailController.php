<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $locale = app()->getLocale() ?: 'en';

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('localized.dashboard', ['locale' => $locale], absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('localized.dashboard', ['locale' => $locale], absolute: false).'?verified=1');
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

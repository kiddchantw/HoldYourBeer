<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * This method handles special characters in email addresses and provides
     * comprehensive error handling for mail sending failures.
     *
     * Rate Limiting: 3 attempts per minute, 10 per hour (via password-reset throttle)
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Normalize email to lowercase for consistency
        // This handles special characters and ensures case-insensitive matching
        $email = strtolower(trim($request->input('email', '')));

        // Check if user is an OAuth user without a password
        // In this case, we don't send an email but guide them to use OAuth login
        $user = User::where('email', $email)->first();
        if ($user && $user->canSetPasswordWithoutCurrent()) {
            return back()->with('status', __('passwords.oauth_hint'));
        }

        $request->merge(['email' => $email]);

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => __('validation.email.required'),
            'email.email' => __('validation.email.email'),
            'email.max' => __('validation.email.max'),
        ]);

        try {
            // Attempt to send the password reset link
            $status = Password::sendResetLink(
                $request->only('email')
            );

            // Log successful password reset request for security monitoring
            if ($status === Password::RESET_LINK_SENT) {
                Log::info('Password reset link sent', [
                    'email' => $email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return back()->with('status', __($status));
            }

            // Log failed attempts (user not found, etc.)
            Log::warning('Password reset link failed', [
                'email' => $email,
                'status' => $status,
                'ip' => $request->ip(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);

        } catch (\Exception $e) {
            // Log mail sending failures with full error details
            Log::error('Password reset email sending failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);

            // Return a user-friendly error message without exposing technical details
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __('passwords.mail_error')
                        ?: 'Unable to send password reset email. Please try again later or contact support.'
                ]);
        }
    }
}

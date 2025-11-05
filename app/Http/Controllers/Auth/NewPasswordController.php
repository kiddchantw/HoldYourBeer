<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * This method handles special characters in email addresses and provides
     * comprehensive error handling for password reset operations.
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

        $request->merge(['email' => $email]);

        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'token.required' => __('validation.token.required'),
            'email.required' => __('validation.email.required'),
            'email.email' => __('validation.email.email'),
            'password.required' => __('validation.password.required'),
            'password.confirmed' => __('validation.password.confirmed'),
        ]);

        try {
            // Attempt to reset the user's password
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user) use ($request, $email) {
                    $user->forceFill([
                        'password' => Hash::make($request->password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    // Log successful password reset for security monitoring
                    Log::info('Password successfully reset', [
                        'user_id' => $user->id,
                        'email' => $email,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return redirect()
                    ->route('login')
                    ->with('status', __($status));
            }

            // Log failed password reset attempts
            Log::warning('Password reset failed', [
                'email' => $email,
                'status' => $status,
                'ip' => $request->ip(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);

        } catch (\Exception $e) {
            // Log unexpected errors during password reset
            Log::error('Password reset exception', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __('passwords.reset_error')
                        ?: 'Unable to reset password. Please try again or request a new reset link.'
                ]);
        }
    }
}

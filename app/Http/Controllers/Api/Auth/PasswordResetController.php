<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * For OAuth users without a password, returns a hint to use OAuth login.
     * For other users (local or OAuth with password), sends a reset link.
     * For non-existent emails, returns a generic message to prevent email enumeration.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower($request->email);
        $user = \App\Models\User::where('email', $email)->first();

        // OAuth users without password should use OAuth login
        if ($user && $user->canSetPasswordWithoutCurrent()) {
            return response()->json([
                'message' => __('passwords.oauth_hint'),
                'may_require_oauth' => true,
            ]);
        }

        // Send reset link for users with password (local or OAuth)
        if ($user) {
            $status = Password::sendResetLink(['email' => $email]);

            if ($status != Password::RESET_LINK_SENT) {
                throw ValidationException::withMessages([
                    'email' => [__($status)],
                ]);
            }

            return response()->json([
                'message' => __($status),
                'may_require_oauth' => false,
            ]);
        }

        // Non-existent email: return generic message to prevent enumeration
        return response()->json([
            'message' => __('passwords.sent'),
            'may_require_oauth' => false,
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Reset the user's password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }
}

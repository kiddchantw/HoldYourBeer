<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     *
     * OAuth users (Google, Apple, etc.) can set a password without current_password
     * Local users must provide their current password to change it
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // OAuth users setting password for the first time don't need current_password
        if ($user->isOAuthUser()) {
            $validated = $request->validateWithBag('updatePassword', [
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);

            // After setting password, user can login with both OAuth and email/password
            // Note: We keep provider field to track original registration method
        } else {
            // Local users or legacy users must provide current password
            $validated = $request->validateWithBag('updatePassword', [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}

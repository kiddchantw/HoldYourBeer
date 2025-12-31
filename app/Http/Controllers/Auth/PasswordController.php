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
     * Password validation rules:
     * - OAuth users WITHOUT password (first time): no current_password required
     * - OAuth users WITH password (update): current_password required
     * - Local/Legacy users: current_password required
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Only OAuth users who haven't set a password yet can skip current_password
        if ($user->canSetPasswordWithoutCurrent()) {
            $validated = $request->validateWithBag('updatePassword', [
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);
        } else {
            // All users with existing password must provide current_password
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

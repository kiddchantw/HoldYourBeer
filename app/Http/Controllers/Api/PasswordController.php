<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordController extends Controller
{
    /**
     * Update the user's password (API JSON response).
     *
     * Password validation rules:
     * - OAuth users WITHOUT password (first time): no current_password required
     * - OAuth users WITH password (update): current_password required
     * - Local/Legacy users: current_password required
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only OAuth users who haven't set a password yet can skip current_password
        if ($user->canSetPasswordWithoutCurrent()) {
            $validated = $request->validate([
                'password' => ['required', PasswordRule::defaults(), 'confirmed'],
            ]);
        } else {
            // All users with existing password must provide current_password
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', PasswordRule::defaults(), 'confirmed'],
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['status' => 'password-updated']);
    }
}



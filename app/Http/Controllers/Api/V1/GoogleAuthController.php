<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GoogleAuthRequest;
use App\Models\User;
use App\Services\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @group V1 - Authentication
 *
 * APIs for Google OAuth authentication (Version 1)
 */
class GoogleAuthController extends Controller
{
    /**
     * Authenticate with Google ID Token
     *
     * Verify Google ID Token and create/login user, returning an authentication token.
     *
     * @unauthenticated
     *
     * @bodyParam id_token string required The Google ID Token received from Google Sign-In. Example: eyJhbGciOiJSUzI1NiIsImtpZCI6...
     *
     * @response 200 {
     *   "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "email_verified_at": "2025-11-07T10:00:00.000000Z",
     *     "provider": "google",
     *     "created_at": "2025-11-07T10:00:00.000000Z",
     *     "updated_at": "2025-11-07T10:00:00.000000Z"
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Invalid Google ID token."
     * }
     *
     * @response 500 {
     *   "message": "Failed to verify Google ID token. Please try again."
     * }
     */
    public function authenticate(GoogleAuthRequest $request, GoogleAuthService $googleAuthService): JsonResponse
    {
        try {
            // Verify the ID token
            $payload = $googleAuthService->verifyIdToken($request->id_token);

            if (!$payload) {
                return response()->json([
                    'message' => 'Invalid Google ID token.',
                ], 401);
            }

            // Extract user information from the token payload
            $googleId = $payload['sub'] ?? null;
            $email = strtolower($payload['email'] ?? '');
            $name = $payload['name'] ?? '';
            $emailVerified = ($payload['email_verified'] ?? false) === true;

            if (!$googleId || !$email) {
                return response()->json([
                    'message' => 'Invalid token payload. Missing required fields.',
                ], 401);
            }

            // Find or create user based on email
            $user = User::where('email', $email)->first();

            if ($user) {
                // Update user name if it has changed from Google
                if ($user->name !== $name) {
                    $user->update(['name' => $name]);
                }

                // Update email_verified_at if verified by Google but not in our system
                if ($emailVerified && !$user->email_verified_at) {
                    $user->update(['email_verified_at' => now()]);
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'email_verified_at' => $emailVerified ? now() : null,
                    'password' => Hash::make(Str::random(32)), // Random password for OAuth users
                ]);
            }

            // Generate Sanctum token
            $token = $user->createToken('google_auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'provider' => $user->provider,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ], 200);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Google OAuth authentication failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to verify Google ID token. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}

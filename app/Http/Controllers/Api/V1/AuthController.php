<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group V1 - Authentication
 *
 * APIs for user authentication (Version 1)
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * Create a new user account and receive an authentication token and refresh token.
     *
     * @unauthenticated
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email address. Must be unique. Example: john@example.com
     * @bodyParam password string required The user's password. Must be at least 8 characters. Example: password123
     * @bodyParam password_confirmation string required Password confirmation. Must match password. Example: password123
     * @bodyParam device_name string optional The device name for tracking. Example: iPhone 13
     *
     * @response 201 {
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "email_verified_at": null,
     *       "provider": "local",
     *       "created_at": "2025-11-05T10:00:00.000000Z",
     *       "updated_at": "2025-11-05T10:00:00.000000Z"
     *     },
     *     "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz",
     *     "refresh_token": "xyz789abc456def123ghi890jkl567mno234pqr901stu678vwx345yz012abc",
     *     "token_type": "Bearer",
     *     "expires_in": 10800
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The email has already been taken.",
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Normalize email to lowercase for consistency
        $validated = $request->validated();
        $validated['email'] = strtolower($validated['email']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        // Create access token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Create refresh token
        $refreshTokenData = RefreshToken::generate(
            $user,
            $request->device_name,
            $request->userAgent()
        );

        // Get expiration time in seconds
        $expiresIn = config('sanctum.expiration', 180) * 60;

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'provider' => $user->provider,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'token' => $token,
                'refresh_token' => $refreshTokenData['plain_token'],
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
            ]
        ], 201);
    }

    /**
     * Login and get token
     *
     * Authenticate a user with email and password and receive an API token and refresh token.
     *
     * @unauthenticated
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password. Example: password123
     * @bodyParam device_name string optional The device name for tracking. Example: iPhone 13
     *
     * @response 200 {
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "email_verified_at": null,
     *       "provider": "local",
     *       "created_at": "2025-11-05T10:00:00.000000Z",
     *       "updated_at": "2025-11-05T10:00:00.000000Z"
     *     },
     *     "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz",
     *     "refresh_token": "xyz789abc456def123ghi890jkl567mno234pqr901stu678vwx345yz012abc",
     *     "token_type": "Bearer",
     *     "expires_in": 10800
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["These credentials do not match our records."]
     *   }
     * }
     */
    public function token(Request $request): JsonResponse
    {
        // 先將 email 轉為小寫以進行一致性檢查
        $request->merge([
            'email' => strtolower($request->email ?? '')
        ]);

        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ], [
            'email.required' => '電子郵件欄位為必填項目。',
            'email.string' => '電子郵件必須是字串格式。',
            'email.email' => '電子郵件格式不正確。',
            'password.required' => '密碼欄位為必填項目。',
            'password.string' => '密碼必須是字串格式。',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        // Create access token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Create refresh token
        $refreshTokenData = RefreshToken::generate(
            $user,
            $request->device_name,
            $request->userAgent()
        );

        // Get expiration time in seconds
        $expiresIn = config('sanctum.expiration', 180) * 60;

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'provider' => $user->provider,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'token' => $token,
                'refresh_token' => $refreshTokenData['plain_token'],
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
            ]
        ]);
    }

    /**
     * Logout
     *
     * Invalidate the current access token and revoke all refresh tokens for the user.
     *
     * @authenticated
     *
     * @bodyParam refresh_token string optional The refresh token to revoke. If not provided, all refresh tokens will be revoked.
     *
     * @response 200 {
     *   "message": "Logged out successfully."
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        // Delete current access token
        $request->user()->currentAccessToken()->delete();

        // If specific refresh token is provided, revoke only that one
        if ($request->has('refresh_token')) {
            $refreshToken = RefreshToken::validate($request->refresh_token);
            if ($refreshToken && $refreshToken->user_id === $request->user()->id) {
                $refreshToken->revoke();
            }
        } else {
            // Otherwise, revoke all refresh tokens for this user
            RefreshToken::where('user_id', $request->user()->id)->delete();
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Refresh access token
     *
     * Use a valid refresh token to obtain a new access token.
     *
     * @unauthenticated
     *
     * @bodyParam refresh_token string required The refresh token received during login/register. Example: abc123def456ghi789jkl012mno345pqr678stu901vwx234yz567abc890def123
     *
     * @response 200 {
     *   "data": {
     *     "access_token": "2|xyz456abc789def012ghi345jkl678mno901pqr234stu567vwx890yz",
     *     "token_type": "Bearer",
     *     "expires_in": 10800
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Invalid or expired refresh token."
     * }
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $refreshToken = RefreshToken::validate($request->refresh_token);

        if (!$refreshToken) {
            return response()->json([
                'message' => 'Invalid or expired refresh token.',
            ], 401);
        }

        // Mark the refresh token as used
        $refreshToken->markAsUsed();

        // Create new access token
        $accessToken = $refreshToken->user->createToken('auth_token')->plainTextToken;

        // Get expiration time in seconds
        $expiresIn = config('sanctum.expiration', 180) * 60;

        return response()->json([
            'data' => [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
            ]
        ]);
    }
}

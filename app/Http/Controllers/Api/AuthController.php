<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 *
 * APIs for user authentication
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * Create a new user account and receive an authentication token.
     *
     * @unauthenticated
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email address. Must be unique. Example: john@example.com
     * @bodyParam password string required The user's password. Must be at least 8 characters. Example: password123
     * @bodyParam password_confirmation string required Password confirmation. Must match password. Example: password123
     *
     * @response 201 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "email_verified_at": null,
     *     "created_at": "2025-11-05T10:00:00.000000Z",
     *     "updated_at": "2025-11-05T10:00:00.000000Z"
     *   },
     *   "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz"
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

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * Login and get token
     *
     * Authenticate a user with email and password and receive an API token.
     *
     * @unauthenticated
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password. Example: password123
     *
     * @response 200 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "email_verified_at": null,
     *     "created_at": "2025-11-05T10:00:00.000000Z",
     *     "updated_at": "2025-11-05T10:00:00.000000Z"
     *   },
     *   "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz"
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

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Logout
     *
     * Invalidate the current access token and log the user out.
     *
     * @authenticated
     *
     * @response 200 {
     *   "message": "Logged out successfully."
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
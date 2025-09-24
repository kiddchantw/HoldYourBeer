<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     * POST /api/register
     */
    public function register(Request $request): JsonResponse
    {
        // 先將 email 轉為小寫以進行一致性檢查
        $request->merge([
            'email' => strtolower($request->email ?? '')
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ], [
            'name.required' => '姓名欄位為必填項目。',
            'name.string' => '姓名必須是字串格式。',
            'name.max' => '姓名不得超過 255 個字元。',
            'email.required' => '電子郵件欄位為必填項目。',
            'email.string' => '電子郵件必須是字串格式。',
            'email.email' => '電子郵件格式不正確。',
            'email.max' => '電子郵件不得超過 255 個字元。',
            'email.unique' => '此電子郵件已被註冊。',
            'password.required' => '密碼欄位為必填項目。',
            'password.string' => '密碼必須是字串格式。',
            'password.min' => '密碼至少需要 8 個字元。',
            'password.max' => '密碼不得超過 255 個字元。',
            'password_confirmation.required' => '密碼確認欄位為必填項目。',
            'password_confirmation.string' => '密碼確認必須是字串格式。',
            'password_confirmation.same' => '密碼確認與密碼不符。',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email, // 已經是小寫
            'password' => Hash::make($request->password),
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
     * Request an API token (Login)
     * POST /api/sanctum/token
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
     * Log the user out (Invalidate the token).
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
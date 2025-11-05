<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class FirebaseAuthController extends Controller
{
    public function __construct(
        protected FirebaseAuthService $firebaseAuth
    ) {}

    /**
     * Authenticate user with Firebase ID Token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        try {
            $verifiedToken = $this->firebaseAuth->verifyIdToken($request->id_token);
            $user = $this->firebaseAuth->findOrCreateUser($verifiedToken);

            // Update FCM token if provided
            if ($request->fcm_token) {
                $this->firebaseAuth->updateFcmToken($user, $request->fcm_token);
            }

            return response()->json([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'provider' => $user->provider,
                    'firebase_uid' => $user->firebase_uid,
                ],
            ], 200);

        } catch (FailedToVerifyToken $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired Firebase token'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => 'An error occurred during authentication'
            ], 500);
        }
    }

    /**
     * Get authenticated user info
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'provider' => $user->provider,
                'firebase_uid' => $user->firebase_uid,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ],
        ], 200);
    }

    /**
     * Update FCM token for push notifications
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        $this->firebaseAuth->updateFcmToken($user, $request->fcm_token);

        return response()->json([
            'message' => 'FCM token updated successfully',
        ], 200);
    }

    /**
     * Logout user (clear FCM token)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->firebaseAuth->clearFcmToken($user);

        return response()->json([
            'message' => 'Logout successful',
        ], 200);
    }
}

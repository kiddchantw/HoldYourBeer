<?php

namespace App\Services;

use App\Models\User;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Illuminate\Support\Facades\Log;

class FirebaseAuthService
{
    public function __construct(
        protected Auth $auth
    ) {}

    /**
     * Verify Firebase ID Token and return the decoded token
     *
     * @param string $idToken
     * @return \Kreait\Firebase\Auth\Token
     * @throws FailedToVerifyToken
     */
    public function verifyIdToken(string $idToken)
    {
        try {
            return $this->auth->verifyIdToken($idToken);
        } catch (FailedToVerifyToken $e) {
            Log::error('Firebase token verification failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Find or create user from Firebase token
     *
     * @param \Kreait\Firebase\Auth\Token $verifiedToken
     * @return User
     */
    public function findOrCreateUser($verifiedToken): User
    {
        $firebaseUid = $verifiedToken->claims()->get('sub');
        $email = $verifiedToken->claims()->get('email');
        $name = $verifiedToken->claims()->get('name');
        $provider = $this->getProviderFromToken($verifiedToken);

        // Find by firebase_uid first
        $user = User::where('firebase_uid', $firebaseUid)->first();

        if ($user) {
            // Update FCM token if provided
            return $user;
        }

        // Check if user exists with same email (for account linking)
        $user = User::where('email', $email)->first();

        if ($user) {
            // Link existing account with Firebase
            $user->update([
                'firebase_uid' => $firebaseUid,
                'provider' => $provider,
            ]);
            return $user;
        }

        // Create new user
        return User::create([
            'name' => $name ?? 'User',
            'email' => $email,
            'firebase_uid' => $firebaseUid,
            'provider' => $provider,
            'password' => bcrypt(str()->random(32)), // Random password for Firebase users
            'email_verified_at' => now(), // Firebase users are already verified
        ]);
    }

    /**
     * Update FCM token for user
     *
     * @param User $user
     * @param string|null $fcmToken
     * @return void
     */
    public function updateFcmToken(User $user, ?string $fcmToken): void
    {
        if ($fcmToken) {
            $user->update(['fcm_token' => $fcmToken]);
        }
    }

    /**
     * Get provider from Firebase token
     *
     * @param \Kreait\Firebase\Auth\Token $verifiedToken
     * @return string
     */
    protected function getProviderFromToken($verifiedToken): string
    {
        $firebaseData = $verifiedToken->claims()->get('firebase');

        if (isset($firebaseData['sign_in_provider'])) {
            $provider = $firebaseData['sign_in_provider'];

            // Map Firebase providers to our format
            return match($provider) {
                'google.com' => 'google',
                'apple.com' => 'apple',
                'password' => 'email',
                default => $provider,
            };
        }

        return 'firebase';
    }

    /**
     * Clear FCM token (for logout)
     *
     * @param User $user
     * @return void
     */
    public function clearFcmToken(User $user): void
    {
        $user->update(['fcm_token' => null]);
    }
}

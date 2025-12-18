<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmailVerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * This endpoint is public but protected by signed URL middleware (for HTTP URLs)
     * or manual signature verification (for custom scheme URLs).
     * It can verify email even if the user is not logged in.
     */
    public function verify(Request $request, $id, $hash): JsonResponse
    {
        // Find the user by ID
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found. The verification link may be invalid or expired.',
                'verified' => false,
            ], 404);
        }

        // Verify the hash matches the user's email
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link.',
                'verified' => false,
            ], 403);
        }

        // Verify signature and expiration
        $expires = $request->query('expires');
        $signature = $request->query('signature');

        if (!$expires || !$signature) {
            return response()->json([
                'message' => 'Missing expires or signature parameter.',
                'verified' => false,
            ], 403);
        }

        // Check expiration
        if (time() > (int) $expires) {
            return response()->json([
                'message' => 'Verification link has expired.',
                'verified' => false,
            ], 403);
        }

        // Verify signature using Laravel's signed URL validation
        // Laravel's URL::temporarySignedRoute() calculates signature as:
        // hash_hmac('sha256', $url . '?expires=' . $expires, $key)
        // Where $url is the full absolute URL without query parameters
        $routeName = 'v1.verification.verify';
        $routeUrl = route($routeName, [
            'id' => $id,
            'hash' => $hash,
        ], true); // true = absolute URL

        // Build the URL with expires parameter (as Laravel does it)
        // Laravel's format: $url . '?expires=' . $expires
        $urlWithExpires = $routeUrl . '?expires=' . $expires;

        // Calculate signature: hash_hmac('sha256', $urlWithExpires, $key)
        $expectedSignature = hash_hmac('sha256', $urlWithExpires, config('app.key'));

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json([
                'message' => 'Invalid signature.',
                'verified' => false,
            ], 403);
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
                'verified' => true,
            ]);
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email verified successfully.',
            'verified' => true,
        ]);
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => '電子郵件已經驗證過了',
                'verified' => true,
            ], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => '驗證郵件已重新發送',
        ]);
    }
}

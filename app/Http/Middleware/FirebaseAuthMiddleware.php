<?php

namespace App\Http\Middleware;

use App\Services\FirebaseAuthService;
use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Symfony\Component\HttpFoundation\Response;

class FirebaseAuthMiddleware
{
    public function __construct(
        protected FirebaseAuthService $firebaseAuth
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'No authentication token provided'
            ], 401);
        }

        try {
            $verifiedToken = $this->firebaseAuth->verifyIdToken($token);
            $user = $this->firebaseAuth->findOrCreateUser($verifiedToken);

            // Set authenticated user for the request
            auth()->setUser($user);
            $request->setUserResolver(fn () => $user);

        } catch (FailedToVerifyToken $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired token'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => 'An error occurred during authentication'
            ], 500);
        }

        return $next($request);
    }
}

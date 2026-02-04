<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'DENY');

        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Prevent information disclosure
        $response->headers->set('X-Powered-By', 'HoldYourBeer');

        // Content Security Policy
        $this->setContentSecurityPolicy($response);

        // CORS headers (for API routes)
        if ($request->is('api/*')) {
            $this->setCorsHeaders($response, $request);
        }

        return $response;
    }

    /**
     * Set Content Security Policy headers.
     */
    protected function setContentSecurityPolicy(Response $response): void
    {
        // Check if running in local environment with Vite dev server
        $isLocal = app()->environment('local');

        $csp = [
            "default-src 'self'",
            "img-src 'self' data: https: blob:",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        // Script sources - add localhost:5173 for Vite in local env
        if ($isLocal) {
            $csp[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 https://cdn.jsdelivr.net https://unpkg.com https://www.googletagmanager.com https://*.googletagmanager.com";
            $csp[] = "style-src 'self' 'unsafe-inline' http://localhost:5173 https://cdn.jsdelivr.net https://unpkg.com https://fonts.bunny.net https://fonts.googleapis.com";
            $csp[] = "font-src 'self' data: http://localhost:5173 https://cdn.jsdelivr.net https://fonts.bunny.net https://fonts.gstatic.com";
            $csp[] = "connect-src 'self' ws://localhost:5173 http://localhost:5173 https://www.google-analytics.com https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com " . env('API_URL', config('app.url'));
        } else {
            $csp[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://www.googletagmanager.com https://*.googletagmanager.com";
            $csp[] = "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://fonts.bunny.net https://fonts.googleapis.com";
            $csp[] = "font-src 'self' data: https://cdn.jsdelivr.net https://fonts.bunny.net https://fonts.gstatic.com";
            $csp[] = "connect-src 'self' https://www.google-analytics.com https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com " . env('API_URL', config('app.url'));
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));
    }

    /**
     * Set CORS headers for API routes.
     */
    protected function setCorsHeaders(Response $response, Request $request): void
    {
        $allowedOrigins = [
            env('FRONTEND_URL', 'http://localhost:3000'),
            env('APP_URL', 'http://localhost'),
        ];

        $origin = $request->header('Origin');

        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
        $response->headers->set('Access-Control-Expose-Headers', 'X-API-Version, X-RateLimit-Limit, X-RateLimit-Remaining');
        $response->headers->set('Access-Control-Max-Age', '3600');
    }
}

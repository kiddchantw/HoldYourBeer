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
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://fonts.bunny.net",
            "img-src 'self' data: https: blob:",
            "font-src 'self' data: https://cdn.jsdelivr.net https://fonts.bunny.net",
            "connect-src 'self' " . env('API_URL', config('app.url')),
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

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

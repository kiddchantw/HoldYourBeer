<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Deprecation Middleware
 *
 * Adds deprecation warning headers to non-versioned API routes.
 * This middleware informs API clients that they should migrate to versioned endpoints.
 */
class ApiDeprecation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add deprecation warning headers
        $response->headers->set('X-API-Deprecation', 'true');
        $response->headers->set('X-API-Deprecation-Info', 'Non-versioned API endpoints are deprecated. Please use /api/v1/* endpoints.');
        $response->headers->set('X-API-Sunset-Date', '2026-12-31'); // Planned removal date
        $response->headers->set('X-API-Current-Version', 'v1');

        // Add Link header pointing to API documentation
        $response->headers->set('Link', '<' . config('app.url') . '/docs>; rel="deprecation"');

        return $response;
    }
}

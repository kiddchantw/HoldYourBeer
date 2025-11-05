<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate unique request ID
        $requestId = $request->header('X-Request-ID', uniqid('req_', true));

        // Add request ID to request for downstream use
        $request->headers->set('X-Request-ID', $requestId);

        // Record start time
        $startTime = microtime(true);

        // Process the request
        $response = $next($request);

        // Calculate duration
        $duration = (microtime(true) - $startTime) * 1000; // milliseconds

        // Add request ID to response headers
        $response->headers->set('X-Request-ID', $requestId);

        // Log the request
        $this->logRequest($request, $response, $duration, $requestId);

        // Log slow requests separately
        if ($duration > 1000) { // > 1 second
            $this->logSlowRequest($request, $response, $duration, $requestId);
        }

        return $response;
    }

    /**
     * Log the API request.
     */
    protected function logRequest(Request $request, Response $response, float $duration, string $requestId): void
    {
        $logData = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'url' => $request->fullUrl(),
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'timestamp' => now()->toIso8601String(),
        ];

        // Add query parameters (exclude sensitive data)
        if ($request->query()) {
            $logData['query'] = $this->sanitizeData($request->query());
        }

        // Add request size
        $logData['request_size'] = strlen($request->getContent());

        // Add response size
        $logData['response_size'] = strlen($response->getContent());

        // Determine log level based on status code
        $logLevel = $this->getLogLevel($response->getStatusCode());

        Log::channel('api')->log($logLevel, 'API Request', $logData);
    }

    /**
     * Log slow requests for performance monitoring.
     */
    protected function logSlowRequest(Request $request, Response $response, float $duration, string $requestId): void
    {
        Log::channel('api')->warning('Slow API Request', [
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'duration_ms' => round($duration, 2),
            'user_id' => $request->user()?->id,
            'status' => $response->getStatusCode(),
        ]);
    }

    /**
     * Get appropriate log level based on HTTP status code.
     */
    protected function getLogLevel(int $statusCode): string
    {
        return match (true) {
            $statusCode >= 500 => 'error',
            $statusCode >= 400 => 'warning',
            $statusCode >= 300 => 'info',
            default => 'info',
        };
    }

    /**
     * Sanitize data to remove sensitive information.
     */
    protected function sanitizeData(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'apiKey', 'access_token'];

        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitizeData($value);
            }
        }

        return $data;
    }
}

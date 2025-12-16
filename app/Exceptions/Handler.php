<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        // Only apply custom JSON error handling to API requests
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->renderApiException($request, $e);
        }

        // WEB: Handle ModelNotFoundException
        if ($e instanceof ModelNotFoundException) {
            return back()->with('error', '找不到指定的資源');
        }

        return parent::render($request, $e);
    }

    /**
     * Render API exception responses.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function renderApiException($request, Throwable $e): JsonResponse
    {
        // Handle BusinessLogicException
        if ($e instanceof BusinessLogicException) {
            return response()->json([
                'error_code' => $e->getErrorCode(),
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        }

        // Handle ValidationException (422 Unprocessable Entity)
        if ($e instanceof ValidationException) {
            return response()->json([
                'error_code' => 'VAL_001',
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        }

        // Handle AuthenticationException (401 Unauthorized)
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'error_code' => 'AUTH_001',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Handle AuthorizationException (403 Forbidden) - BEFORE AccessDeniedHttpException
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'error_code' => 'AUTH_002',
                'message' => 'This action is unauthorized.',
            ], 403);
        }

        // Handle AccessDeniedHttpException (403 Forbidden) - wrapped AuthorizationException
        if ($e instanceof AccessDeniedHttpException) {
            return response()->json([
                'error_code' => 'AUTH_002',
                'message' => 'This action is unauthorized.',
            ], 403);
        }

        // Handle ModelNotFoundException (404 Not Found)
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'error_code' => 'RES_001',
                'message' => 'Resource not found.',
            ], 404);
        }

        // Handle NotFoundHttpException (404 Not Found)
        if ($e instanceof NotFoundHttpException) {
            $message = config('app.debug')
                ? $e->getMessage()
                : 'The requested resource was not found.';

            return response()->json([
                'error_code' => 'RES_001',
                'message' => $message,
            ], 404);
        }

        // Handle HttpException (various status codes) - MUST BE AFTER more specific exceptions
        if ($e instanceof HttpException) {
            // Check if it's wrapped authorization exception
            if ($e->getStatusCode() === 403) {
                return response()->json([
                    'error_code' => 'AUTH_002',
                    'message' => 'This action is unauthorized.',
                ], 403);
            }

            return response()->json([
                'error_code' => 'HTTP_' . $e->getStatusCode(),
                'message' => $e->getMessage() ?: 'An error occurred.',
            ], $e->getStatusCode());
        }

        // Handle generic exceptions
        $statusCode = $this->isHttpException($e) ? $e->getStatusCode() : 500;

        // In production, hide detailed error messages
        if (config('app.debug')) {
            return response()->json([
                'error_code' => 'SYS_001',
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ], $statusCode);
        }

        // Production: generic error message
        return response()->json([
            'error_code' => 'SYS_001',
            'message' => 'An internal server error occurred. Please try again later.',
        ], 500);
    }
}

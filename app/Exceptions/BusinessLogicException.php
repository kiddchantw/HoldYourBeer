<?php

namespace App\Exceptions;

use Exception;

/**
 * Custom exception for business logic errors.
 * Provides safe error messages that don't leak sensitive information.
 */
class BusinessLogicException extends Exception
{
    /**
     * Error code for categorizing the error.
     */
    protected string $errorCode;

    /**
     * Create a new business logic exception instance.
     *
     * @param string $message User-safe error message
     * @param string $errorCode Error code (e.g., 'BIZ_001')
     * @param int $statusCode HTTP status code
     */
    public function __construct(
        string $message,
        string $errorCode = 'BIZ_000',
        protected int $statusCode = 400
    ) {
        parent::__construct($message);
        $this->errorCode = $errorCode;
    }

    /**
     * Get the error code.
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get the HTTP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render()
    {
        return response()->json([
            'error_code' => $this->errorCode,
            'message' => $this->getMessage(),
        ], $this->statusCode);
    }
}

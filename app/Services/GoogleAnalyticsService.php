<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Google Analytics 4 Event Tracking Service
 *
 * This service provides methods to track custom events in Google Analytics 4.
 * Events are logged to the application log and can be sent to GA4 via
 * frontend JavaScript or Measurement Protocol API.
 */
class GoogleAnalyticsService
{
    /**
     * Track user registration event
     *
     * @param int $userId
     * @param string $method Registration method (email, google, apple)
     * @return void
     */
    public function trackUserRegistration(int $userId, string $method = 'email'): void
    {
        $this->logEvent('user_registration', [
            'user_id' => $userId,
            'method' => $method,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Track user login event
     *
     * @param int $userId
     * @param string $method Login method (email, google, apple)
     * @return void
     */
    public function trackUserLogin(int $userId, string $method = 'email'): void
    {
        $this->logEvent('user_login', [
            'user_id' => $userId,
            'method' => $method,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Track user logout event
     *
     * @param int $userId
     * @return void
     */
    public function trackUserLogout(int $userId): void
    {
        $this->logEvent('user_logout', [
            'user_id' => $userId,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Track beer creation event
     *
     * @param int $userId
     * @param int $beerId
     * @param string $brandName
     * @param string|null $beerName
     * @return void
     */
    public function trackBeerCreation(int $userId, int $beerId, string $brandName, ?string $beerName = null): void
    {
        $this->logEvent('beer_created', [
            'user_id' => $userId,
            'beer_id' => $beerId,
            'brand_name' => $brandName,
            'beer_name' => $beerName,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Track beer count increment event
     *
     * @param int $userId
     * @param int $beerId
     * @param int $previousCount
     * @param int $newCount
     * @return void
     */
    public function trackBeerCountIncrement(int $userId, int $beerId, int $previousCount, int $newCount): void
    {
        $this->logEvent('beer_count_incremented', [
            'user_id' => $userId,
            'beer_id' => $beerId,
            'previous_count' => $previousCount,
            'new_count' => $newCount,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Track beer count decrement event
     *
     * @param int $userId
     * @param int $beerId
     * @param int $previousCount
     * @param int $newCount
     * @return void
     */
    public function trackBeerCountDecrement(int $userId, int $beerId, int $previousCount, int $newCount): void
    {
        $this->logEvent('beer_count_decremented', [
            'user_id' => $userId,
            'beer_id' => $beerId,
            'previous_count' => $previousCount,
            'new_count' => $newCount,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Track search event
     *
     * @param int $userId
     * @param string $searchQuery
     * @param int $resultsCount
     * @return void
     */
    public function trackSearch(int $userId, string $searchQuery, int $resultsCount): void
    {
        $this->logEvent('search', [
            'user_id' => $userId,
            'search_query' => $searchQuery,
            'results_count' => $resultsCount,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Track error event
     *
     * @param string $errorType
     * @param string $errorMessage
     * @param int|null $userId
     * @return void
     */
    public function trackError(string $errorType, string $errorMessage, ?int $userId = null): void
    {
        $this->logEvent('error', [
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'user_id' => $userId,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Log event to application log
     *
     * In production, these events can be:
     * 1. Sent to GA4 via Measurement Protocol API
     * 2. Queued for batch processing
     * 3. Pushed to frontend for client-side tracking
     *
     * @param string $eventName
     * @param array $parameters
     * @return void
     */
    protected function logEvent(string $eventName, array $parameters): void
    {
        if (!config('services.google_analytics.enabled')) {
            return;
        }

        Log::channel('analytics')->info("GA4 Event: {$eventName}", $parameters);

        // TODO: Future enhancement - Send to GA4 Measurement Protocol API
        // $this->sendToMeasurementProtocol($eventName, $parameters);
    }

    /**
     * Send event to GA4 Measurement Protocol API (Future implementation)
     *
     * @param string $eventName
     * @param array $parameters
     * @return void
     */
    protected function sendToMeasurementProtocol(string $eventName, array $parameters): void
    {
        // Future implementation:
        // - Use GA4 Measurement Protocol API
        // - Send events from server-side
        // - Useful for non-web events (cron jobs, API calls, etc.)
    }
}

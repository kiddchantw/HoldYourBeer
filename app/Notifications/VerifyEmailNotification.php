<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('[HoldYourBeer] ' . __('Verify Email Address'))
            ->greeting(__('Hello') . ' ' . $notifiable->name . '!')
            ->line(__('Thanks for signing up!'))
            ->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Email'), $verificationUrl)
            ->line(__('This verification link will expire in :minutes minutes.', ['minutes' => Config::get('auth.verification.expire', 60)]))
            ->line(__('If you did not create an account, no further action is required.'))
            ->salutation(__('Regards') . ',' . "\n" . __('HoldYourBeer Team'));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * Supports both Universal Links (production) and custom URL scheme (development).
     */
    protected function verificationUrl($notifiable)
    {
        $locale = app()->getLocale() ?: 'en';
        $expires = Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60));
        $id = $notifiable->getKey();
        $hash = sha1($notifiable->getEmailForVerification());

        // Get mobile link configuration
        $linkMode = Config::get('app.mobile_link_mode', 'universal');
        $isLocalEnv = app()->environment('local');

        // Build path: {locale}/verify-email/{id}/{hash} (for display in email)
        $path = sprintf('%s/verify-email/%d/%s', $locale, $id, $hash);

        // Generate signed URL parameters using API route (for mobile app)
        // Note: We use API route because mobile app calls /api/v1/email/verify/{id}/{hash}
        // Route name is 'v1.verification.verify' (not 'api.v1.verification.verify')
        $signedUrl = URL::temporarySignedRoute(
            'v1.verification.verify',
            $expires,
            [
                'id' => $id,
                'hash' => $hash,
            ]
        );

        // Parse the signed URL to extract query parameters (expires & signature)
        $parsedUrl = parse_url($signedUrl);
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
        }

        // Extract expires and signature from signed URL
        $expiresParam = $queryParams['expires'] ?? null;
        $signatureParam = $queryParams['signature'] ?? null;

        // Build query string
        $queryString = http_build_query([
            'expires' => $expiresParam,
            'signature' => $signatureParam,
        ]);

        // Determine URL format based on configuration
        // In local environment, default to Custom URL Scheme unless explicitly set to 'universal'
        $useCustomScheme = ($linkMode === 'scheme') || ($isLocalEnv && $linkMode !== 'universal');

        if ($useCustomScheme) {
            // Development: Use custom URL scheme (e.g., holdyourbeer://en/verify-email/...)
            // Note: Custom URL Scheme format is scheme://path (two slashes)
            // The path will be parsed by Flutter app and prefixed with / for routing
            $scheme = Config::get('app.mobile_scheme', 'holdyourbeer');
            return sprintf('%s://%s?%s', $scheme, $path, $queryString);
        }

        // Production or Local HTTP (when explicitly set to 'universal'): Use HTTP/HTTPS URL
        // For local env, use APP_URL; for production, use mobile_web_base
        if ($isLocalEnv) {
            $baseUrl = rtrim(Config::get('app.url', 'http://localhost'), '/');
        } else {
            $baseUrl = rtrim(Config::get('app.mobile_web_base', 'https://holdyourbeers.com'), '/');
        }
        return sprintf('%s/%s?%s', $baseUrl, $path, $queryString);
    }
}

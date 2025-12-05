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
     */
    protected function verificationUrl($notifiable)
    {
        $locale = app()->getLocale() ?: 'en';

        return URL::temporarySignedRoute(
            'localized.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'locale' => $locale,
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}

<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('[HoldYourBeer] ' . __('Reset Password'))
            ->greeting(__('Hello') . ' ' . $notifiable->name . '!')
            ->line(__('You are receiving this email because we received a password reset request for your account.'))
            ->action(__('Reset Password'), $url)
            ->line(__('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60)]))
            ->line(__('If you did not request a password reset, no further action is required.'))
            ->salutation(__('Regards') . ',' . "\n" . __('HoldYourBeer Team'));
    }

    /**
     * Get the reset URL for the given notifiable.
     */
    protected function resetUrl($notifiable)
    {
        $locale = app()->getLocale() ?: 'en';

        return url(route('localized.password.reset', [
            'locale' => $locale,
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}

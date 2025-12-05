<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetPasswordNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_generates_correct_mail_message(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $token = 'test-reset-token';
        $notification = new ResetPasswordNotification($token);
        $mailMessage = $notification->toMail($user);

        $this->assertEquals('[HoldYourBeer] Reset Password', $mailMessage->subject);
        $this->assertStringContainsString('Hello Test User!', $mailMessage->greeting);
        $this->assertCount(2, $mailMessage->introLines);
        $this->assertStringContainsString('You are receiving this email', $mailMessage->introLines[0]);
    }

    public function test_reset_url_contains_token_and_email(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $token = 'test-reset-token-123';
        $notification = new ResetPasswordNotification($token);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString($token, $mailMessage->actionUrl);
        $this->assertStringContainsString('email=test%40example.com', $mailMessage->actionUrl);
    }

    public function test_reset_url_contains_locale(): void
    {
        app()->setLocale('zh-TW');

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $token = 'test-reset-token';
        $notification = new ResetPasswordNotification($token);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('/zh-TW/reset-password/', $mailMessage->actionUrl);
    }

    public function test_notification_supports_multilingual(): void
    {
        // Test English
        app()->setLocale('en');
        $user = User::factory()->create(['name' => 'Test User']);
        $notification = new ResetPasswordNotification('test-token');
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('You are receiving this email', $mailMessage->introLines[0]);
        $this->assertEquals('Reset Password', $mailMessage->actionText);

        // Test Traditional Chinese
        app()->setLocale('zh-TW');
        $user = User::factory()->create(['name' => 'Test User']);
        $notification = new ResetPasswordNotification('test-token');
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('您收到這封電子郵件', $mailMessage->introLines[0]);
        $this->assertEquals('重設密碼', $mailMessage->actionText);
    }

    public function test_notification_includes_expiration_time(): void
    {
        $user = User::factory()->create();
        $notification = new ResetPasswordNotification('test-token');
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('60', $mailMessage->introLines[1]);
    }
}

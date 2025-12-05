<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class VerifyEmailNotificationTest extends TestCase
{
    use RefreshDatabase;
    public function test_notification_generates_correct_subject(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $notification = new VerifyEmailNotification();
        $mailMessage = $notification->toMail($user);

        $this->assertEquals('[HoldYourBeer] Verify Email Address', $mailMessage->subject);
    }

    public function test_notification_includes_user_name(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'test@example.com',
        ]);

        $notification = new VerifyEmailNotification();
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('John Doe', $mailMessage->greeting);
    }

    public function test_verification_url_contains_locale(): void
    {
        app()->setLocale('zh-TW');

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $notification = new VerifyEmailNotification();
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('/zh-TW/verify-email/', $mailMessage->actionUrl);
    }

    public function test_verification_url_is_signed(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $notification = new VerifyEmailNotification();
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('signature=', $mailMessage->actionUrl);
        $this->assertStringContainsString('expires=', $mailMessage->actionUrl);
    }

    public function test_notification_supports_multilingual(): void
    {
        // Test English
        app()->setLocale('en');
        $user = User::factory()->create(['name' => 'Test User']);
        $notification = new VerifyEmailNotification();
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('Thanks for signing up!', $mailMessage->introLines[0]);
        $this->assertEquals('Verify Email', $mailMessage->actionText);

        // Test Traditional Chinese
        app()->setLocale('zh-TW');
        $user = User::factory()->create(['name' => 'Test User']);
        $notification = new VerifyEmailNotification();
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('感謝您註冊', $mailMessage->introLines[0]);
        $this->assertEquals('驗證電子郵件', $mailMessage->actionText);
    }
}

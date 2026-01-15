<?php

namespace Tests\Feature\Notifications\Slack;

use App\Models\User;
use App\Notifications\Slack\UserRegisteredNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserRegisteredNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 設定測試用的 Slack Token
        Config::set('services.slack.notifications.bot_user_oauth_token', 'xoxb-test-token');

        // Fake Slack API 回應
        Http::fake([
            'slack.com/*' => Http::response(['ok' => true], 200),
        ]);
    }

    public function test_new_user_creation_triggers_notification(): void
    {
        // Arrange
        Notification::fake();

        // Act - 建立新用戶
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        // Assert - 應該發送通知
        Notification::assertSentTo(
            $user,
            UserRegisteredNotification::class
        );
    }

    public function test_notification_uses_correct_slack_channel(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $channel = $user->routeNotificationForSlack(new UserRegisteredNotification($user));

        // Assert
        $this->assertEquals('#holdyourbeer-users', $channel);
    }

    public function test_notification_implements_should_queue(): void
    {
        // Arrange
        $notification = new UserRegisteredNotification(
            User::factory()->make()
        );

        // Assert - 應該實作 ShouldQueue
        $this->assertInstanceOf(
            \Illuminate\Contracts\Queue\ShouldQueue::class,
            $notification
        );
    }

    public function test_notification_via_returns_slack_channel(): void
    {
        // Arrange
        $notification = new UserRegisteredNotification(
            User::factory()->make()
        );

        // Act
        $channels = $notification->via(null);

        // Assert
        $this->assertEquals(['slack'], $channels);
    }


}

<?php

namespace Tests\Feature\Feature;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $this->markTestSkipped('Web interface not implemented');
        $response = $this->get('/en/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $this->markTestSkipped('Web interface not implemented');
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/en/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo(
            $user,
            \App\Notifications\ResetPasswordNotification::class
        );
    }

    public function test_api_forgot_password_sends_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->postJson(route('v1.password.email'), [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);

        Notification::assertSentTo(
            $user,
            \App\Notifications\ResetPasswordNotification::class
        );
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $this->markTestSkipped('Web interface not implemented');
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->get('/en/reset-password/' . $token . '?email=' . $user->email);

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $this->markTestSkipped('Web interface not implemented');
        Event::fake();

        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->post('/en/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
        Event::assertDispatched(PasswordReset::class);
    }

    public function test_password_cannot_be_reset_with_invalid_token(): void
    {
        $this->markTestSkipped('Web interface not implemented');
        $user = User::factory()->create();
        $oldPassword = $user->password;

        $response = $this->post('/en/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertEquals($oldPassword, $user->fresh()->password);
    }

    public function test_api_password_reset_with_valid_token(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson(route('v1.password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
        Event::assertDispatched(PasswordReset::class);
    }

    public function test_api_password_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();
        $oldPassword = $user->password;

        $response = $this->postJson(route('v1.password.store'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422);
        $this->assertEquals($oldPassword, $user->fresh()->password);
    }

    public function test_password_reset_requires_email(): void
    {
        $response = $this->postJson(route('v1.password.email'), [
            'email' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_password_reset_requires_valid_email(): void
    {
        $response = $this->postJson(route('v1.password.email'), [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_password_must_be_confirmed(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson(route('v1.password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_password_reset_throttling(): void
    {
        $user = User::factory()->create();

        // Make multiple requests
        for ($i = 0; $i < 7; $i++) {
            $response = $this->postJson(route('v1.password.email'), [
                'email' => $user->email,
            ]);
        }

        // 7th request should be throttled
        $response->assertStatus(429);
    }

    // ===== OAuth 忘記密碼流程優化測試 =====

    public function test_oauth_user_without_password_receives_oauth_hint(): void
    {
        Notification::fake();

        // 建立 Google OAuth 用戶（無密碼）
        $user = User::factory()->create([
            'email' => 'oauth@example.com',
            'provider' => 'google',
            'provider_id' => 'google-123',
            'password' => null,
        ]);

        $response = $this->postJson(route('v1.password.email'), [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'may_require_oauth' => true,
        ]);
        $response->assertJsonFragment([
            'message' => __('passwords.oauth_hint'),
        ]);

        // 確認不會發送郵件給任何用戶
        Notification::assertNothingSentTo($user);
    }

    public function test_oauth_user_with_password_receives_reset_link(): void
    {
        Notification::fake();

        // 建立 Google OAuth 用戶（已設定密碼）
        $user = User::factory()->create([
            'email' => 'oauth-with-pw@example.com',
            'provider' => 'google',
            'provider_id' => 'google-456',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(route('v1.password.email'), [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'may_require_oauth' => false,
        ]);

        // 確認會發送重設郵件
        Notification::assertSentTo(
            $user,
            \App\Notifications\ResetPasswordNotification::class
        );
    }

    public function test_local_user_receives_password_reset_link(): void
    {
        Notification::fake();

        // 建立本地用戶
        $user = User::factory()->create([
            'email' => 'local@example.com',
            'provider' => 'local',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(route('v1.password.email'), [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'may_require_oauth' => false,
        ]);

        // 確認會發送重設郵件
        Notification::assertSentTo(
            $user,
            \App\Notifications\ResetPasswordNotification::class
        );
    }

    public function test_non_existent_email_receives_generic_message(): void
    {
        Notification::fake();

        $response = $this->postJson(route('v1.password.email'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'may_require_oauth' => false,
        ]);
        $response->assertJsonFragment([
            'message' => __('passwords.sent'),
        ]);

        // 確認沒有發送任何通知（因為用戶不存在）
        Notification::assertNothingSent();
    }
}

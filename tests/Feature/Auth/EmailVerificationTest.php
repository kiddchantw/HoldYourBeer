<?php

namespace Tests\Feature\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_notification_sent_after_registration(): void
    {
        $this->markTestSkipped('Web interface not implemented');
        Notification::fake();

        $response = $this->post('/en/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        Notification::assertSentTo(
            $user,
            \App\Notifications\VerifyEmailNotification::class
        );
    }

    public function test_api_register_sends_verification_notification(): void
    {
        Notification::fake();

        $response = $this->postJson(route('v1.register'), [
            'name' => 'Test User',
            'email' => 'apitest@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $user = User::where('email', 'apitest@example.com')->first();

        Notification::assertSentTo(
            $user,
            \App\Notifications\VerifyEmailNotification::class
        );
    }

    public function test_user_can_verify_email_with_valid_link(): void
    {
        $this->markTestSkipped('Web interface not implemented');
        Event::fake();

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'localized.verification.verify',
            now()->addMinutes(60),
            [
                'locale' => 'en',
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        Event::assertDispatched(Verified::class);
    }

    public function test_user_cannot_verify_with_invalid_hash(): void
    {
        $this->markTestSkipped('Web interface not implemented');
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'localized.verification.verify',
            now()->addMinutes(60),
            [
                'locale' => 'en',
                'id' => $user->id,
                'hash' => 'invalid-hash',
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_user_cannot_verify_with_expired_link(): void
    {
        $this->markTestSkipped('Web interface not implemented');
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'localized.verification.verify',
            now()->subMinutes(10), // Expired
            [
                'locale' => 'en',
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_api_can_resend_verification_notification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('v1.verification.send'));

        $response->assertStatus(200);

        Notification::assertSentTo(
            $user,
            \App\Notifications\VerifyEmailNotification::class
        );
    }

    public function test_verified_user_cannot_resend_verification(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('v1.verification.send'));

        $response->assertStatus(400);
        $response->assertJson(['verified' => true]);
    }

    public function test_api_verify_endpoint_marks_email_as_verified(): void
    {
        Event::fake();

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'v1.verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user, 'sanctum')
            ->getJson($verificationUrl);

        $response->assertStatus(200);
        $response->assertJson(['verified' => true]);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        Event::assertDispatched(Verified::class);
    }
}

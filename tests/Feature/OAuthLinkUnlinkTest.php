<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserOAuthProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OAuthLinkUnlinkTest extends TestCase
{
    use RefreshDatabase;

    protected function mockSocialiteUser($provider, $id, $name, $email)
    {
        $socialiteUser = (new SocialiteUser())->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ]);

        Socialite::shouldReceive('driver')
            ->with($provider)
            ->andReturnSelf();
        Socialite::shouldReceive('stateless')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->andReturn($socialiteUser);

        return $socialiteUser;
    }

    #[Test]
    public function authenticated_user_can_link_oauth_provider()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user);

        $this->mockSocialiteUser('google', 'google_123', 'Test User', 'test@example.com');

        $response = $this->get(route('social.link', ['provider' => 'google']));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify OAuth provider is linked
        $this->assertTrue($user->hasOAuthProvider('google'));
        $this->assertDatabaseHas('user_oauth_providers', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google_123',
        ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_link_oauth_provider()
    {
        $response = $this->get(route('social.link', ['provider' => 'google']));

        // Should be redirected to login by auth middleware
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function cannot_link_oauth_with_different_email()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user);

        // Mock OAuth user with different email
        $this->mockSocialiteUser('google', 'google_123', 'Test User', 'different@example.com');

        $response = $this->get(route('social.link', ['provider' => 'google']));

        $response->assertRedirect();
        $response->assertSessionHasErrors('oauth_link');

        // Verify OAuth provider is NOT linked
        $this->assertFalse($user->hasOAuthProvider('google'));
    }

    #[Test]
    public function cannot_link_oauth_already_linked_to_another_user()
    {
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user2 = User::factory()->create([
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Link OAuth to user1
        UserOAuthProvider::create([
            'user_id' => $user1->id,
            'provider' => 'google',
            'provider_id' => 'google_123',
            'provider_email' => 'user1@example.com',
            'linked_at' => now(),
        ]);

        // Try to link same OAuth to user2
        $this->actingAs($user2);
        $this->mockSocialiteUser('google', 'google_123', 'User2', 'user2@example.com');

        $response = $this->get(route('social.link', ['provider' => 'google']));

        $response->assertRedirect();
        $response->assertSessionHasErrors('oauth_link');

        // Verify OAuth is still only linked to user1
        $this->assertTrue($user1->fresh()->hasOAuthProvider('google'));
        $this->assertFalse($user2->fresh()->hasOAuthProvider('google'));
    }

    #[Test]
    public function user_can_unlink_oauth_provider()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Link OAuth provider
        UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google_123',
            'provider_email' => 'test@example.com',
            'linked_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('social.unlink', ['provider' => 'google']));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify OAuth provider is unlinked
        $this->assertFalse($user->fresh()->hasOAuthProvider('google'));
        $this->assertDatabaseMissing('user_oauth_providers', [
            'user_id' => $user->id,
            'provider' => 'google',
        ]);
    }

    #[Test]
    public function cannot_unlink_last_authentication_method()
    {
        // Create user with only OAuth provider (password exists but will be considered as not usable)
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Remove password using raw SQL to bypass Laravel validation
        \DB::statement('UPDATE users SET password = NULL WHERE id = ?', [$user->id]);
        $user = User::find($user->id);

        // Link only one OAuth provider
        UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google_123',
            'provider_email' => 'test@example.com',
            'linked_at' => now(),
        ]);

        // User should have only 1 auth method (OAuth only, no password)
        $this->assertEquals(1, $user->getAuthMethodsCount());

        $this->actingAs($user);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('social.unlink', ['provider' => 'google']));

        $response->assertRedirect();
        $response->assertSessionHasErrors('oauth_unlink');

        // Verify OAuth provider is still linked
        $this->assertTrue($user->fresh()->hasOAuthProvider('google'));
    }

    #[Test]
    public function user_with_password_and_oauth_can_unlink_oauth()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Link OAuth provider
        UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google_123',
            'provider_email' => 'test@example.com',
            'linked_at' => now(),
        ]);

        // User has 2 auth methods: password + OAuth
        $this->assertEquals(2, $user->getAuthMethodsCount());

        $this->actingAs($user);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('social.unlink', ['provider' => 'google']));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify OAuth is unlinked
        $this->assertFalse($user->fresh()->hasOAuthProvider('google'));
        // User still has password
        $this->assertEquals(1, $user->fresh()->getAuthMethodsCount());
    }

    #[Test]
    public function user_with_multiple_oauth_can_unlink_one()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Remove password using raw SQL to bypass Laravel validation
        \DB::statement('UPDATE users SET password = NULL WHERE id = ?', [$user->id]);
        $user = User::find($user->id);

        // Link two OAuth providers
        UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google_123',
            'provider_email' => 'test@example.com',
            'linked_at' => now(),
        ]);

        UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'apple',
            'provider_id' => 'apple_456',
            'provider_email' => 'test@example.com',
            'linked_at' => now(),
        ]);

        // User has 2 OAuth methods (no password)
        $this->assertEquals(2, $user->getAuthMethodsCount());

        $this->actingAs($user);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('social.unlink', ['provider' => 'google']));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify Google is unlinked but Apple remains
        $this->assertFalse($user->fresh()->hasOAuthProvider('google'));
        $this->assertTrue($user->fresh()->hasOAuthProvider('apple'));
        $this->assertEquals(1, $user->fresh()->getAuthMethodsCount());
    }

    #[Test]
    public function unauthenticated_user_cannot_unlink_oauth()
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('social.unlink', ['provider' => 'google']));

        $response->assertRedirect(route('login'));
    }
}

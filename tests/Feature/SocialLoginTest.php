<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Related specifications: spec/features/third_party_login.feature
 *
 * Scenarios covered:
 * - User login with Google account
 * - User login with Apple account
 * - Existing user login with Google account
 * - Existing user login with Apple account
 * - Social login failure handling
 *
 * Test coverage:
 * - OAuth authentication flow with email as unique identifier
 * - Social provider integration (Google, Apple)
 * - Automatic account linking via email
 * - Error handling for failed authentication
 * - Email verification for OAuth users
 *
 * Note: This implementation uses email as the unique identifier.
 * No separate provider fields are stored in the database.
 */
class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function mockSocialiteUser($provider, $id, $name, $email)
    {
        $socialiteUser = (new SocialiteUser())->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ]);

        Socialite::shouldReceive('driver')->with($provider)->andReturnSelf();
        Socialite::shouldReceive('user')->andReturn($socialiteUser);

        return $socialiteUser;
    }

    #[Test]
    public function user_can_login_with_google()
    {
        $this->mockSocialiteUser('google', 'google_id_123', 'Google User', 'google@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'google']));

        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
            'name' => 'Google User',
        ]);

        // Verify email is verified for OAuth users
        $user = User::where('email', 'google@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
    }

    #[Test]
    public function user_can_login_with_apple()
    {
        $this->mockSocialiteUser('apple', 'apple_id_123', 'Apple User', 'apple@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'apple']));

        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'apple@example.com',
            'name' => 'Apple User',
        ]);

        // Verify email is verified for OAuth users
        $user = User::where('email', 'apple@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
    }

    #[Test]
    public function existing_user_can_login_with_google()
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->mockSocialiteUser('google', 'google_id_456', 'Existing User', 'existing@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'google']));

        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $this->assertAuthenticatedAs($user);

        // Email is the unique identifier - no provider fields needed
        $this->assertDatabaseHas('users', [
            'email' => 'existing@example.com',
        ]);
    }

    #[Test]
    public function existing_user_can_login_with_apple()
    {
        $user = User::factory()->create([
            'email' => 'existing2@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->mockSocialiteUser('apple', 'apple_id_456', 'Existing User 2', 'existing2@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'apple']));

        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $this->assertAuthenticatedAs($user);

        // Email is the unique identifier - no provider fields needed
        $this->assertDatabaseHas('users', [
            'email' => 'existing2@example.com',
        ]);
    }

    #[Test]
    public function social_login_redirects_to_login_on_failure()
    {
        Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
        Socialite::shouldReceive('user')->andThrow(new \Exception('Socialite error'));

        $response = $this->get(route('social.callback', ['provider' => 'google']));

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('social_login');
    }
}

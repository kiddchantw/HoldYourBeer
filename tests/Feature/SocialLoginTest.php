<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
            'provider' => 'google',
            'provider_id' => 'google_id_123',
            'google_id' => 'google_id_123',
        ]);
    }

    #[Test]
    public function user_can_login_with_apple()
    {
        $this->mockSocialiteUser('apple', 'apple_id_123', 'Apple User', 'apple@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'apple']));

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'apple@example.com',
            'provider' => 'apple',
            'provider_id' => 'apple_id_123',
            'apple_id' => 'apple_id_123',
        ]);
    }

    #[Test]
    public function existing_user_can_link_google_account()
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->mockSocialiteUser('google', 'google_id_456', 'Existing User', 'existing@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'google']));

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'email' => 'existing@example.com',
            'provider' => 'google',
            'provider_id' => 'google_id_456',
            'google_id' => 'google_id_456',
        ]);
    }

    #[Test]
    public function existing_user_can_link_apple_account()
    {
        $user = User::factory()->create([
            'email' => 'existing2@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->mockSocialiteUser('apple', 'apple_id_456', 'Existing User 2', 'existing2@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'apple']));

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'email' => 'existing2@example.com',
            'provider' => 'apple',
            'provider_id' => 'apple_id_456',
            'apple_id' => 'apple_id_456',
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

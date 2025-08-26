<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \spec\features\user-registration.feature
 *
 * Scenarios covered:
 * - Web-based user authentication
 * - Login form accessibility
 * - User credential validation
 * - Authentication state management
 * - User logout functionality
 *
 * Test coverage:
 * - Login screen rendering
 * - User authentication flow
 * - Invalid password handling
 * - User logout process
 * - Authentication middleware functionality
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/en/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        // Debug: Check if user was created correctly
        $this->assertDatabaseHas('users', ['email' => $user->email]);

        // Disable CSRF protection for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $response = $this->post('/en/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Debug: Check response status and any errors
        if ($response->status() !== 302) {
            $this->fail('Expected redirect (302) but got: ' . $response->status() . '. Response: ' . $response->content());
        }

        $this->assertAuthenticated();
        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        // Disable CSRF protection for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->post('/en/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        // Disable CSRF protection for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $response = $this->actingAs($user)->post('/en/logout');

        $this->assertGuest();
        $response->assertRedirect('/en');
    }
}

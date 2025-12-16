<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Related specifications: spec/features/user-registration.feature
 *
 * Scenarios covered:
 * - New user registration form accessibility
 * - User registration with valid credentials
 * - Authentication state after successful registration
 *
 * Test coverage:
 * - Registration screen rendering
 * - User account creation process
 * - Post-registration authentication
 * - Dashboard redirection after registration
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/en/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // Disable CSRF protection for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $response = $this->post('/en/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
    }
}

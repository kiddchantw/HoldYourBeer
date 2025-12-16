<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Related specifications: spec/features/user-registration.feature
 *
 * Scenarios covered:
 * - A new user registers with valid credentials
 * - A user tries to register with an existing email
 *
 * Test coverage:
 * - User registration validation
 * - Duplicate email prevention
 * - Authentication state after registration
 * - Dashboard redirection after successful registration
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a new user can register and is redirected to the dashboard.
     *
     * @return void
     */
    public function test_a_new_user_can_register()
    {
        // Disable CSRF protection for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $response = $this->post('/en/register', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
    }

    /**
     * Test that a user cannot register with an existing email.
     *
     * @return void
     */
    public function test_a_user_cannot_register_with_an_existing_email()
    {
        User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
        ]);

        // Disable CSRF protection for this test
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $response = $this->post('/en/register', [
            'name' => 'John Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }
}

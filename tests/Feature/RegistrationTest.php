<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
            'password' => 'password',
            'password_confirmation' => 'password',
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
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }
}

<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Related specifications: spec/features/user-registration.feature
 * Related specifications: spec/api/api.yaml (Authentication endpoints)
 *
 * Scenarios covered:
 * - API user registration
 * - API user login with token generation
 * - API user logout
 * - API authentication validation
 * - API error handling for invalid credentials
 *
 * Test coverage:
 * - API registration endpoint functionality
 * - Bearer token generation and validation
 * - API login endpoint with Sanctum authentication
 * - API logout token revocation
 * - API validation error responses
 * - JSON response structure validation
 */
class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_register_a_new_user()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function it_returns_validation_errors_on_failed_registration()
    {
        $data = [
            'name' => '', // Invalid name
            'email' => 'not-an-email', // Invalid email
            'password' => 'short', // Invalid password
            'password_confirmation' => 'different', // Password confirmation doesn't match
        ];

        $response = $this->postJson('/api/v1/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function it_can_login_a_user_and_return_a_token()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $data = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'token',
            ]);
    }

    #[Test]
    public function it_returns_an_error_on_failed_login()
    {
        $user = User::factory()->create();

        $data = [
            'email' => $user->email,
            'password' => 'wrong-password',
        ];

        $response = $this->postJson('/api/v1/login', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_can_logout_an_authenticated_user()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully.']);
    }

    #[Test]
    public function it_returns_an_error_if_unauthenticated_user_tries_to_logout()
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }

    #[Test]
    public function register_endpoint_returns_oauth_status_fields()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'is_oauth_user',
                    'can_set_password_without_current',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ])
            ->assertJson([
                'user' => [
                    'is_oauth_user' => false,
                    'can_set_password_without_current' => false,
                ],
            ]);
    }

    #[Test]
    public function login_endpoint_returns_oauth_status_fields()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $data = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/login', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'is_oauth_user',
                    'can_set_password_without_current',
                ],
                'token',
            ])
            ->assertJson([
                'user' => [
                    'is_oauth_user' => false,
                    'can_set_password_without_current' => false,
                ],
            ]);
    }
}

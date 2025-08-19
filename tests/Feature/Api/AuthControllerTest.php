<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_register_a_new_user()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson('/api/register', $data);

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

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function it_can_login_a_user_and_return_a_token()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $data = [
            'email' => $user->email,
            'password' => 'password',
        ];

        $response = $this->postJson('/api/login', $data);

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

        $response = $this->postJson('/api/login', $data);

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

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully.']);
    }

    #[Test]
    public function it_returns_an_error_if_unauthenticated_user_tries_to_logout()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }
}

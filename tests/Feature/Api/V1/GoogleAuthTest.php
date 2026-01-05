<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Services\GoogleAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper method to mock Google Auth Service
     */
    protected function mockGoogleAuthService(mixed $payload): void
    {
        $googleAuthServiceMock = $this->createMock(GoogleAuthService::class);
        $googleAuthServiceMock->method('verifyIdToken')
            ->willReturn($payload);

        $this->app->instance(GoogleAuthService::class, $googleAuthServiceMock);
    }

    /**
     * Test successful Google authentication with new user
     */
    public function test_successful_google_authentication_creates_new_user(): void
    {
        // Mock Google Auth Service
        $this->mockGoogleAuthService([
            'sub' => 'google_user_123',
            'email' => 'newuser@example.com',
            'name' => 'New User',
            'email_verified' => true,
        ]);

        // Make the request
        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'valid_id_token',
        ]);

        // Assert response structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Assert user data
        $response->assertJson([
            'user' => [
                'name' => 'New User',
                'email' => 'newuser@example.com',
            ],
        ]);

        // Assert user was created in database
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User',
        ]);

        // Assert email is verified
        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
    }

    /**
     * Test successful Google authentication with existing user
     */
    public function test_successful_google_authentication_with_existing_user(): void
    {
        // Create existing user
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'name' => 'Old Name',
            'email_verified_at' => null,
        ]);

        // Mock Google Client
        $this->mockGoogleAuthService([
            'sub' => 'google_user_456',
            'email' => 'existing@example.com',
            'name' => 'Updated Name',
            'email_verified' => true,
        ]);

        // Make the request
        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'valid_id_token',
        ]);

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $existingUser->id,
                    'name' => 'Updated Name',
                    'email' => 'existing@example.com',
                ],
            ]);

        // Assert user was updated (name and email_verified_at)
        $existingUser->refresh();
        $this->assertEquals('Updated Name', $existingUser->name);
        $this->assertNotNull($existingUser->email_verified_at);

        // Assert no new user was created
        $this->assertCount(1, User::all());
    }

    /**
     * Test authentication fails with invalid ID token
     */
    public function test_authentication_fails_with_invalid_token(): void
    {
        // Mock Google Client to return false (invalid token)
        $this->mockGoogleAuthService(false);

        // Make the request
        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'invalid_id_token',
        ]);

        // Assert error response
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid Google ID token.',
            ]);

        // Assert no user was created
        $this->assertCount(0, User::all());
    }

    /**
     * Test authentication fails with missing ID token
     */
    public function test_authentication_fails_without_id_token(): void
    {
        // Make the request without id_token
        $response = $this->postJson('/api/v1/auth/google', []);

        // Assert validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_token']);
    }

    /**
     * Test authentication fails with missing required payload fields
     */
    public function test_authentication_fails_with_incomplete_token_payload(): void
    {
        // Mock Google Client with incomplete payload (missing email)
        $this->mockGoogleAuthService([
            'sub' => 'google_user_789',
            'name' => 'Test User',
            // Missing 'email' field
        ]);

        // Make the request
        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'incomplete_token',
        ]);

        // Assert error response
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid token payload. Missing required fields.',
            ]);
    }

    /**
     * Test email is stored in lowercase
     */
    public function test_email_is_normalized_to_lowercase(): void
    {
        // Mock Google Client
        $this->mockGoogleAuthService([
            'sub' => 'google_user_999',
            'email' => 'TestUser@EXAMPLE.COM',
            'name' => 'Test User',
            'email_verified' => true,
        ]);

        // Make the request
        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'valid_id_token',
        ]);

        // Assert response
        $response->assertStatus(200);

        // Assert email is stored in lowercase
        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
        ]);
    }

    /**
     * Test token generation and authentication
     */
    public function test_generated_token_can_be_used_for_authentication(): void
    {
        // Mock Google Client
        $this->mockGoogleAuthService([
            'sub' => 'google_user_111',
            'email' => 'authtest@example.com',
            'name' => 'Auth Test User',
            'email_verified' => true,
        ]);

        // Authenticate with Google
        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'valid_token',
        ]);

        $response->assertStatus(200);
        $token = $response->json('token');

        // Use the token to access protected endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/user');

        $response->assertStatus(200)
            ->assertJson([
                'email' => 'authtest@example.com',
                'name' => 'Auth Test User',
            ]);
    }

    /**
     * Test unverified Google email does not set email_verified_at
     */
    public function test_unverified_google_email_does_not_set_verified_timestamp(): void
    {
        // Mock Google Client
        $this->mockGoogleAuthService([
            'sub' => 'google_user_222',
            'email' => 'unverified@example.com',
            'name' => 'Unverified User',
            'email_verified' => false,
        ]);

        // Make the request
        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'valid_token',
        ]);

        $response->assertStatus(200);

        // Assert email_verified_at is null
        $user = User::where('email', 'unverified@example.com')->first();
        $this->assertNull($user->email_verified_at);
    }

    /**
     * Test google auth endpoint returns oauth status fields
     */
    public function test_google_auth_endpoint_returns_oauth_status_fields(): void
    {
        // Mock Google Auth Service
        $this->mockGoogleAuthService([
            'sub' => 'google_user_333',
            'email' => 'oauthtest@example.com',
            'name' => 'OAuth Test User',
            'email_verified' => true,
        ]);

        // Make the request
        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'valid_id_token',
        ]);

        // Assert response includes oauth status fields
        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'is_oauth_user',
                    'can_set_password_without_current',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'user' => [
                    'is_oauth_user' => true,
                    'can_set_password_without_current' => true,
                ],
            ]);
    }
}

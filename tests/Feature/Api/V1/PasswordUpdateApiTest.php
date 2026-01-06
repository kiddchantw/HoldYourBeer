<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\Helpers\CreatesOAuthUsers;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * API Password Update Test
 *
 * Tests the PUT /api/v1/password endpoint for:
 * - OAuth users (first time set / update)
 * - Local users
 * - Legacy users
 */
class PasswordUpdateApiTest extends TestCase
{
    use RefreshDatabase, CreatesOAuthUsers;

    /**
     * OAuth user without password can set password without current_password
     */
    #[Test]
    public function oauth_user_without_password_can_set_password_via_api()
    {
        $user = $this->createOAuthUser('google', [
            'email' => 'oauth-api@example.com',
            'password' => null,
        ], [
            'provider_id' => 'google_api_123',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/password', [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'password-updated']);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    /**
     * OAuth user with existing password can update WITHOUT current_password (NEW LOGIC)
     */
    #[Test]
    public function oauth_user_with_password_can_update_without_current_password_via_api()
    {
        $user = $this->createOAuthUser('google', [
            'email' => 'oauth-api-pass@example.com',
            'password' => Hash::make('ExistingPassword123!'),
        ], [
            'provider_id' => 'google_api_456',
        ]);

        Sanctum::actingAs($user);

        // NEW: OAuth users can update password without current_password
        $response = $this->putJson('/api/v1/password', [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'password-updated']);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    /**
     * OAuth user with existing password can update with correct current_password
     */
    #[Test]
    public function oauth_user_with_password_can_update_with_correct_current_password_via_api()
    {
        $user = $this->createOAuthUser('google', [
            'email' => 'oauth-api-update@example.com',
            'password' => Hash::make('ExistingPassword123!'),
        ], [
            'provider_id' => 'google_api_789',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/password', [
            'current_password' => 'ExistingPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'password-updated']);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    /**
     * Local user must provide current_password
     */
    #[Test]
    public function local_user_must_provide_current_password_via_api()
    {
        $user = $this->createLocalUser([
            'email' => 'local-api@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/password', [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    /**
     * Local user can update password with correct current_password
     */
    #[Test]
    public function local_user_can_update_password_via_api()
    {
        $user = $this->createLocalUser([
            'email' => 'local-api-update@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'password-updated']);

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    /**
     * Unauthenticated user cannot access password update endpoint
     */
    #[Test]
    public function unauthenticated_user_cannot_update_password()
    {
        $response = $this->putJson('/api/v1/password', [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Password validation rules are enforced
     */
    #[Test]
    public function password_validation_rules_are_enforced()
    {
        $user = $this->createOAuthUser('google', [
            'email' => 'validation-test@example.com',
            'password' => null,
        ], [
            'provider_id' => 'google_validation',
        ]);

        Sanctum::actingAs($user);

        // Password too short
        $response = $this->putJson('/api/v1/password', [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);

        // Password confirmation mismatch
        $response = $this->putJson('/api/v1/password', [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Wrong current_password is rejected
     */
    #[Test]
    public function wrong_current_password_is_rejected()
    {
        $user = $this->createLocalUser([
            'email' => 'wrong-pass@example.com',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/password', [
            'current_password' => 'WrongPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }
}

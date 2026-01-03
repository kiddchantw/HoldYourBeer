<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserOAuthProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OAuthUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that OAuth user created via Google login has null password
     *
     * @test
     */
    public function oauth_user_created_via_google_login_has_null_password(): void
    {
        // Arrange: Create a user as if they registered via Google OAuth
        $user = User::create([
            'name' => 'Google User',
            'email' => 'google@example.com',
            'password' => null, // OAuth users should have null password
            'email_verified_at' => now(),
        ]);

        // Create OAuth provider link
        UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google_12345',
            'provider_email' => 'google@example.com',
            'linked_at' => now(),
            'last_used_at' => now(),
        ]);

        // Assert: Password should be null
        $this->assertNull($user->password);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'google@example.com',
            'password' => null,
        ]);
    }

    /**
     * Test that OAuth user without password returns false for hasPassword()
     *
     * @test
     */
    public function oauth_user_without_password_returns_false_for_hasPassword(): void
    {
        // Arrange: Create OAuth user with null password
        $user = User::create([
            'name' => 'OAuth User',
            'email' => 'oauth@example.com',
            'password' => null,
            'email_verified_at' => now(),
        ]);

        UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google_67890',
            'provider_email' => 'oauth@example.com',
            'linked_at' => now(),
            'last_used_at' => now(),
        ]);

        // Assert: hasPassword() should return false
        $this->assertFalse($user->hasPassword());
    }

    /**
     * Test that OAuth user can set password without current password
     *
     * @test
     */
    public function oauth_user_can_set_password_without_current_password(): void
    {
        // Arrange: Create OAuth user with null password
        $user = User::create([
            'name' => 'OAuth User',
            'email' => 'oauth@example.com',
            'password' => null,
            'email_verified_at' => now(),
        ]);

        UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google_99999',
            'provider_email' => 'oauth@example.com',
            'linked_at' => now(),
            'last_used_at' => now(),
        ]);

        // Assert: canSetPasswordWithoutCurrent() should return true
        // This test will FAIL because isOAuthUser() currently checks $this->provider
        // which doesn't exist in our new design
        $this->assertTrue($user->canSetPasswordWithoutCurrent());
    }

    /**
     * Test that local user with password returns true for hasPassword()
     *
     * @test
     */
    public function local_user_with_password_returns_true_for_hasPassword(): void
    {
        // Arrange: Create local user with password
        $user = User::factory()->create([
            'email' => 'local@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Assert: hasPassword() should return true
        $this->assertTrue($user->hasPassword());
    }

    /**
     * Test that local user cannot set password without current password
     *
     * @test
     */
    public function local_user_cannot_set_password_without_current_password(): void
    {
        // Arrange: Create local user with password
        $user = User::factory()->create([
            'email' => 'local@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Assert: canSetPasswordWithoutCurrent() should return false
        $this->assertFalse($user->canSetPasswordWithoutCurrent());
    }
}

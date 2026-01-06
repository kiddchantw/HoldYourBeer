<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\CreatesOAuthUsers;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase, CreatesOAuthUsers;

    /** @test */
    public function oauth_user_without_password_can_set_password_without_current()
    {
        // Arrange: Create an OAuth user without password
        $user = $this->createOAuthUser('google', [
            'password' => null,
        ]);

        // Act & Assert
        $this->assertTrue($user->canSetPasswordWithoutCurrent());
    }

    /** @test */
    public function oauth_user_with_password_can_set_password_without_current()
    {
        // Arrange: Create an OAuth user WITH password
        $user = $this->createOAuthUser('google', [
            'password' => bcrypt('old-password'),
        ]);

        // Act & Assert
        // This is the NEW behavior we want to implement
        $this->assertTrue($user->canSetPasswordWithoutCurrent());
    }

    /** @test */
    public function local_user_with_password_cannot_set_password_without_current()
    {
        // Arrange: Create a local user (no OAuth provider)
        $user = $this->createLocalUser([
            'password' => bcrypt('existing-password'),
        ]);

        // Act & Assert
        $this->assertFalse($user->canSetPasswordWithoutCurrent());
    }

    /** @test */
    public function local_user_without_password_can_set_password_without_current()
    {
        // Arrange: Create a local user without password (edge case)
        $user = $this->createLocalUser([
            'password' => null,
        ]);

        // Act & Assert
        // Even local users without password should be able to set one
        $this->assertTrue($user->canSetPasswordWithoutCurrent());
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Related specifications: spec/features/user-registration.feature (Profile management aspects)
 *
 * Scenarios covered:
 * - User profile information display
 * - Profile information update
 * - Email verification handling
 * - User account deletion
 * - Password verification for account deletion
 *
 * Test coverage:
 * - Profile page accessibility
 * - User information update functionality
 * - Email verification state management
 * - Account deletion with password confirmation
 * - Security validation for profile changes
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password123',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrongpass123',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_profile_page_displays_connected_accounts_section(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        // Since we created lang/en/profile.php, we assume default locale is 'en' or fallback is 'en'
        // We can check for the default English string 'Connected Accounts'
        $response->assertSee('Connected Accounts');
        $response->assertSee('Google');
        // Note: Apple and Facebook are hidden in Phase 1.5
    }

    public function test_profile_page_shows_connected_status(): void
    {
        $user = User::factory()->create();
        \App\Models\UserOAuthProvider::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '123',
            'provider_email' => 'test@gmail.com',
            'linked_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee('Connected');
    }

    public function test_oauth_user_without_password_sees_set_password_ui(): void
    {
        // OAuth user who hasn't set a password yet
        $user = User::factory()->create([
            'email' => 'oauth@example.com',
            'password' => null,
            'provider' => 'google',
            'provider_id' => 'google_123',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        // Should see "Set Password" title
        $response->assertSee('Set Password');
        // Should see the description for first-time password setting
        $response->assertSee('Set a password to enable email/password login');
        // Should NOT see "Current Password" field
        $response->assertDontSee('Current Password');
    }

    public function test_oauth_user_with_password_sees_update_password_ui(): void
    {
        // OAuth user who has already set a password
        $user = User::factory()->create([
            'email' => 'oauth-with-pass@example.com',
            'password' => bcrypt('ExistingPassword123!'),
            'provider' => 'google',
            'provider_id' => 'google_456',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        // Should see "Update Password" title
        $response->assertSee('Update Password');
        // Should see the standard password update description
        $response->assertSee('Ensure your account is using a long, random password');
        // Should see "Current Password" field
        $response->assertSee('Current Password');
    }

    public function test_local_user_sees_update_password_ui(): void
    {
        // Local user (registered with email/password)
        $user = User::factory()->create([
            'email' => 'local@example.com',
            'password' => bcrypt('Password123!'),
            'provider' => 'local',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        // Should see "Update Password" title
        $response->assertSee('Update Password');
        // Should see "Current Password" field
        $response->assertSee('Current Password');
    }
}


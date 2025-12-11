<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OAuthPasswordSetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function oauth_user_can_set_password_without_current_password()
    {
        // Create OAuth user (Google registration)
        $user = User::factory()->create([
            'email' => 'oauth@example.com',
            'password' => Hash::make(random_bytes(16)), // Random password
            'provider' => 'google',
            'provider_id' => 'google_123',
        ]);

        $this->actingAs($user);

        // OAuth user can set password without providing current_password
        $response = $this->put(route('password.update'), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    #[Test]
    public function local_user_must_provide_current_password()
    {
        // Create local user (email/password registration)
        $user = User::factory()->create([
            'email' => 'local@example.com',
            'password' => Hash::make('OldPassword123!'),
            'provider' => 'local',
        ]);

        $this->actingAs($user);

        // Local user must provide current_password
        $response = $this->put(route('password.update'), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password');
    }

    #[Test]
    public function local_user_can_change_password_with_correct_current_password()
    {
        // Create local user
        $user = User::factory()->create([
            'email' => 'local2@example.com',
            'password' => Hash::make('OldPassword123!'),
            'provider' => 'local',
        ]);

        $this->actingAs($user);

        // Provide correct current password
        $response = $this->put(route('password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    #[Test]
    public function oauth_user_can_login_with_both_methods_after_setting_password()
    {
        // Create OAuth user
        $user = User::factory()->create([
            'email' => 'dual@example.com',
            'password' => Hash::make(random_bytes(16)),
            'provider' => 'google',
            'provider_id' => 'google_456',
        ]);

        $this->actingAs($user);

        // Set a password
        $response = $this->put(route('password.update'), [
            'password' => 'DualLogin123!',
            'password_confirmation' => 'DualLogin123!',
        ]);

        $response->assertSessionHasNoErrors();

        // Logout
        $this->post(route('logout'));

        // Now user can login with email + password
        $loginResponse = $this->post(route('login'), [
            'email' => 'dual@example.com',
            'password' => 'DualLogin123!',
        ]);

        $loginResponse->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function legacy_user_without_provider_acts_like_local_user()
    {
        // Create legacy user (provider = null, from before migration)
        $user = User::factory()->create([
            'email' => 'legacy@example.com',
            'password' => Hash::make('LegacyPassword123!'),
            'provider' => null, // Legacy user
        ]);

        $this->actingAs($user);

        // Legacy user must provide current_password (acts like local user)
        $response = $this->put(route('password.update'), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password');

        // With correct current password
        $response2 = $this->put(route('password.update'), [
            'current_password' => 'LegacyPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response2->assertSessionHasNoErrors();
    }
}

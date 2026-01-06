<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Helpers\CreatesOAuthUsers;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * OAuth Password Set/Update Test
 *
 * 測試密碼更新邏輯的安全性：
 * - OAuth 用戶首次設定密碼（password = null）：不需要舊密碼
 * - OAuth 用戶更新密碼（password != null）：需要舊密碼
 * - 本地用戶更新密碼：需要舊密碼
 * - Legacy 用戶（provider = null）：需要舊密碼
 */
class OAuthPasswordSetTest extends TestCase
{
    use RefreshDatabase, CreatesOAuthUsers;

    /**
     * OAuth 用戶首次設定密碼（password = null）不需要提供舊密碼
     */
    #[Test]
    public function oauth_user_without_password_can_set_password_without_current_password()
    {
        // OAuth 用戶首次登入，尚未設定密碼
        $user = $this->createOAuthUser('google', [
            'email' => 'oauth@example.com',
            'password' => null, // OAuth 用戶無密碼
        ], [
            'provider_id' => 'google_123',
        ]);

        $this->actingAs($user);

        // OAuth 用戶首次設定密碼不需要 current_password
        $response = $this->put(route('password.update'), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // 驗證密碼已設定
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    /**
     * OAuth 用戶已設定過密碼時，可以不提供舊密碼更新 (NEW LOGIC)
     * 因為 OAuth 登入本身就是強驗證
     */
    #[Test]
    public function oauth_user_with_existing_password_can_update_without_current_password()
    {
        // OAuth 用戶已設定過密碼
        $user = $this->createOAuthUser('google', [
            'email' => 'oauth-with-pass@example.com',
            'password' => Hash::make('ExistingPassword123!'),
        ], [
            'provider_id' => 'google_456',
        ]);

        $this->actingAs($user);

        // NEW: 不提供舊密碼也可以成功
        $response = $this->put(route('password.update'), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    /**
     * OAuth 用戶已設定過密碼，提供正確舊密碼後可以更新
     */
    #[Test]
    public function oauth_user_with_existing_password_can_update_with_correct_current_password()
    {
        // OAuth 用戶已設定過密碼
        $user = $this->createOAuthUser('google', [
            'email' => 'oauth-update@example.com',
            'password' => Hash::make('ExistingPassword123!'),
        ], [
            'provider_id' => 'google_789',
        ]);

        $this->actingAs($user);

        // 提供正確舊密碼 → 應該成功
        $response = $this->put(route('password.update'), [
            'current_password' => 'ExistingPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    /**
     * 本地用戶必須提供舊密碼
     */
    #[Test]
    public function local_user_must_provide_current_password()
    {
        $user = $this->createLocalUser([
            'email' => 'local@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $this->actingAs($user);

        $response = $this->put(route('password.update'), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password');
    }

    /**
     * 本地用戶提供正確舊密碼後可以更新
     */
    #[Test]
    public function local_user_can_update_password_with_correct_current_password()
    {
        $user = $this->createLocalUser([
            'email' => 'local2@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        $this->actingAs($user);

        $response = $this->put(route('password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    /**
     * OAuth 用戶設定密碼後可以同時使用 OAuth 和 email/password 登入
     */
    #[Test]
    public function oauth_user_can_login_with_both_methods_after_setting_password()
    {
        // OAuth 用戶首次登入，尚未設定密碼
        $user = $this->createOAuthUser('google', [
            'email' => 'dual@example.com',
            'password' => null, // OAuth 用戶無密碼
        ], [
            'provider_id' => 'google_dual',
        ]);

        $this->actingAs($user);

        // 設定密碼
        $response = $this->put(route('password.update'), [
            'password' => 'DualLogin123!',
            'password_confirmation' => 'DualLogin123!',
        ]);

        $response->assertSessionHasNoErrors();

        // 登出
        $this->post(route('logout'));

        // 現在可以使用 email + password 登入
        $loginResponse = $this->post(route('login'), [
            'email' => 'dual@example.com',
            'password' => 'DualLogin123!',
        ]);

        $loginResponse->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Legacy 用戶（provider = null）必須提供舊密碼
     */
    #[Test]
    public function legacy_user_without_provider_must_provide_current_password()
    {
        // Legacy 用戶（沒有 OAuth provider）
        $user = $this->createLocalUser([
            'email' => 'legacy@example.com',
            'password' => Hash::make('LegacyPassword123!'),
        ]);

        $this->actingAs($user);

        // 不提供舊密碼 → 應該失敗
        $response = $this->put(route('password.update'), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password');
    }

    /**
     * Legacy 用戶提供正確舊密碼後可以更新
     */
    #[Test]
    public function legacy_user_can_update_password_with_correct_current_password()
    {
        $user = $this->createLocalUser([
            'email' => 'legacy2@example.com',
            'password' => Hash::make('LegacyPassword123!'),
        ]);

        $this->actingAs($user);

        $response = $this->put(route('password.update'), [
            'current_password' => 'LegacyPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }
}

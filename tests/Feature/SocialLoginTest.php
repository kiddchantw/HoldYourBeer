<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Related specifications: spec/features/third_party_login.feature
 *
 * Scenarios covered:
 * - User login with Google account
 * - User login with Apple account
 * - Existing user login with Google account
 * - Existing user login with Apple account
 * - Social login failure handling
 *
 * Test coverage:
 * - OAuth authentication flow with email as unique identifier
 * - Social provider integration (Google, Apple)
 * - Automatic account linking via email
 * - Error handling for failed authentication
 * - Email verification for OAuth users
 *
 * Note: This implementation uses email as the unique identifier.
 * No separate provider fields are stored in the database.
 */
class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function mockSocialiteUser($provider, $id, $name, $email)
    {
        $socialiteUser = (new SocialiteUser())->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ]);

        Socialite::shouldReceive('driver')
            ->with($provider)
            ->andReturnSelf();
        Socialite::shouldReceive('stateless')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->andReturn($socialiteUser);

        return $socialiteUser;
    }

    #[Test]
    public function user_can_login_with_google()
    {
        $this->mockSocialiteUser('google', 'google_id_123', 'Google User', 'google@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'google']));

        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
            'name' => 'Google User',
        ]);

        // Verify email is verified for OAuth users
        $user = User::where('email', 'google@example.com')->first();
        $this->assertNotNull($user->email_verified_at);

        // Verify OAuth provider link is created
        $this->assertTrue($user->hasOAuthProvider('google'));
        $oauthProvider = $user->oauthProviders()->where('provider', 'google')->first();
        $this->assertNotNull($oauthProvider);
        $this->assertEquals('google_id_123', $oauthProvider->provider_id);
        $this->assertEquals('google@example.com', $oauthProvider->provider_email);
    }

    #[Test]
    public function user_can_login_with_apple()
    {
        $this->mockSocialiteUser('apple', 'apple_id_123', 'Apple User', 'apple@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'apple']));

        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'apple@example.com',
            'name' => 'Apple User',
        ]);

        // Verify email is verified for OAuth users
        $user = User::where('email', 'apple@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
    }

    #[Test]
    public function existing_user_can_login_with_google()
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->mockSocialiteUser('google', 'google_id_456', 'Existing User', 'existing@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'google']));

        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $this->assertAuthenticatedAs($user);

        // Email is the unique identifier - no provider fields needed
        $this->assertDatabaseHas('users', [
            'email' => 'existing@example.com',
        ]);
    }

    #[Test]
    public function unverified_local_user_cannot_login_with_oauth()
    {
        // 🔒 R1 Security Test: 建立未驗證的本地使用者（模擬 Email 註冊但未驗證信箱）
        $user = User::factory()->create([
            'email' => 'unverified@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null, // 未驗證
            'provider' => 'local', // Email 註冊
        ]);

        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertEquals('local', $user->provider);

        // 嘗試用 Google 登入（同一信箱）
        $this->mockSocialiteUser('google', 'google_id_789', 'Unverified User', 'unverified@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'google']));

        // 應該被拒絕並重導向到登入頁面
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('social_login');

        // 確認錯誤訊息
        $errors = session('errors');
        $this->assertStringContainsString('尚未驗證', $errors->first('social_login'));

        // 確認用戶未登入
        $this->assertGuest();

        // 確認用戶資料未被修改
        $user->refresh();
        $this->assertNull($user->email_verified_at);
        $this->assertEquals('local', $user->provider);
    }

    #[Test]
    public function verified_local_user_can_login_with_oauth()
    {
        // ✅ R1 Security Test: 建立已驗證的本地使用者
        $originalVerifiedAt = now()->subDays(7);
        $user = User::factory()->create([
            'email' => 'verified@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => $originalVerifiedAt,
            'provider' => 'local',
        ]);

        // 用 Google 登入（同一信箱）- 應該成功
        $this->mockSocialiteUser('google', 'google_id_999', 'Verified User', 'verified@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'google']));

        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $this->assertAuthenticatedAs($user);

        // 驗證：已驗證的使用者可以成功登入，驗證時間保持不變
        $user->refresh();
        $this->assertEquals(
            $originalVerifiedAt->timestamp,
            $user->email_verified_at->timestamp,
            'Already verified users should keep their original verification timestamp'
        );
    }

    #[Test]
    public function oauth_user_can_login_with_different_oauth_provider()
    {
        // ✅ R1 Security Test: 建立 OAuth 使用者（Google）
        $user = User::factory()->create([
            'email' => 'oauth@example.com',
            'password' => Hash::make('random'),
            'email_verified_at' => now()->subDays(3),
            'provider' => 'google',
            'provider_id' => 'google_123',
        ]);

        // 用 Apple 登入（同一信箱）- 應該成功
        $this->mockSocialiteUser('apple', 'apple_456', 'OAuth User', 'oauth@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'apple']));

        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $this->assertAuthenticatedAs($user);

        // OAuth 用戶可以用不同的 OAuth 提供者登入同一帳號
        $this->assertAuthenticated();
    }

    #[Test]
    public function existing_user_can_login_with_apple()
    {
        $user = User::factory()->create([
            'email' => 'existing2@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->mockSocialiteUser('apple', 'apple_id_456', 'Existing User 2', 'existing2@example.com');

        $response = $this->get(route('social.callback', ['provider' => 'apple']));

        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $this->assertAuthenticatedAs($user);

        // Email is the unique identifier - no provider fields needed
        $this->assertDatabaseHas('users', [
            'email' => 'existing2@example.com',
        ]);
    }

    #[Test]
    public function social_login_redirects_to_login_on_failure()
    {
        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturnSelf();
        Socialite::shouldReceive('stateless')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->andThrow(new \Exception('Socialite error'));

        $response = $this->get(route('social.callback', ['provider' => 'google']));

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('social_login');
    }
}

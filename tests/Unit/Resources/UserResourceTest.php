<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\CreatesOAuthUsers;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase, CreatesOAuthUsers;

    /**
     * Test that user resource returns is_oauth_user true for OAuth users.
     */
    public function test_user_resource_returns_is_oauth_user_true_for_oauth_users(): void
    {
        // Arrange: 建立 OAuth 用戶
        $user = $this->createOAuthUser();

        // Act: 使用 UserResource 轉換
        $resource = new UserResource($user);
        $data = $resource->toArray(request());

        // Assert: 應包含 is_oauth_user 且為 true
        $this->assertArrayHasKey('is_oauth_user', $data);
        $this->assertTrue($data['is_oauth_user']);
    }

    /**
     * Test that user resource returns is_oauth_user false for local users.
     */
    public function test_user_resource_returns_is_oauth_user_false_for_local_users(): void
    {
        // Arrange: 建立本地用戶
        $user = $this->createLocalUser();

        // Act: 使用 UserResource 轉換
        $resource = new UserResource($user);
        $data = $resource->toArray(request());

        // Assert: 應包含 is_oauth_user 且為 false
        $this->assertArrayHasKey('is_oauth_user', $data);
        $this->assertFalse($data['is_oauth_user']);
    }

    /**
     * Test that user resource returns can_set_password_without_current true for OAuth users without password.
     */
    public function test_user_resource_returns_can_set_password_without_current_true_for_oauth_users_without_password(): void
    {
        // Arrange: 建立 OAuth 用戶（無密碼）
        $user = $this->createOAuthUser('google', ['password' => null]);

        // Act: 使用 UserResource 轉換
        $resource = new UserResource($user);
        $data = $resource->toArray(request());

        // Assert: 應包含 can_set_password_without_current 且為 true
        $this->assertArrayHasKey('can_set_password_without_current', $data);
        $this->assertTrue($data['can_set_password_without_current']);
    }

    /**
     * Test that user resource returns can_set_password_without_current false for OAuth users with password.
     */
    public function test_user_resource_returns_can_set_password_without_current_false_for_oauth_users_with_password(): void
    {
        // Arrange: 建立 OAuth 用戶（有密碼）
        $user = $this->createOAuthUser(); // 預設會有密碼（因為我們明確設定 password => null 才會沒有）
        // 手動設定密碼
        $user->password = bcrypt('password123');
        $user->save();

        // Act: 使用 UserResource 轉換
        $resource = new UserResource($user->fresh());
        $data = $resource->toArray(request());

        // Assert: 應包含 can_set_password_without_current 且為 false
        $this->assertArrayHasKey('can_set_password_without_current', $data);
        $this->assertFalse($data['can_set_password_without_current']);
    }

    /**
     * Test that user resource returns can_set_password_without_current false for local users.
     */
    public function test_user_resource_returns_can_set_password_without_current_false_for_local_users(): void
    {
        // Arrange: 建立本地用戶
        $user = $this->createLocalUser();

        // Act: 使用 UserResource 轉換
        $resource = new UserResource($user);
        $data = $resource->toArray(request());

        // Assert: 應包含 can_set_password_without_current 且為 false
        $this->assertArrayHasKey('can_set_password_without_current', $data);
        $this->assertFalse($data['can_set_password_without_current']);
    }
}

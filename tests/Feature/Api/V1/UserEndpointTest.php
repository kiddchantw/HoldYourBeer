<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Helpers\CreatesOAuthUsers;
use Tests\TestCase;

class UserEndpointTest extends TestCase
{
    use RefreshDatabase, CreatesOAuthUsers;

    /**
     * Test get user endpoint returns oauth status fields for local users
     */
    public function test_get_user_endpoint_returns_oauth_status_fields_for_local_users(): void
    {
        // Arrange: 建立本地用戶並認證
        $user = $this->createLocalUser();
        Sanctum::actingAs($user, ['*']);

        // Act: 取得用戶資料
        $response = $this->getJson('/api/v1/user');

        // Assert: 應包含 OAuth 狀態欄位
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'is_oauth_user',
                'can_set_password_without_current',
                'email_verified_at',
                'created_at',
                'updated_at',
            ])
            ->assertJson([
                'is_oauth_user' => false,
                'can_set_password_without_current' => false,
            ]);
    }

    /**
     * Test get user endpoint returns oauth status fields for oauth users without password
     */
    public function test_get_user_endpoint_returns_oauth_status_fields_for_oauth_users_without_password(): void
    {
        // Arrange: 建立 OAuth 用戶（無密碼）並認證
        $user = $this->createOAuthUser('google', ['password' => null]);
        Sanctum::actingAs($user, ['*']);

        // Act: 取得用戶資料
        $response = $this->getJson('/api/v1/user');

        // Assert: 應包含 OAuth 狀態欄位
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'is_oauth_user',
                'can_set_password_without_current',
                'email_verified_at',
                'created_at',
                'updated_at',
            ])
            ->assertJson([
                'is_oauth_user' => true,
                'can_set_password_without_current' => true,
            ]);
    }

    /**
     * Test get user endpoint returns oauth status fields for oauth users with password
     */
    public function test_get_user_endpoint_returns_oauth_status_fields_for_oauth_users_with_password(): void
    {
        // Arrange: 建立 OAuth 用戶（有密碼）並認證
        $user = $this->createOAuthUser();
        // 手動設定密碼
        $user->password = bcrypt('password123');
        $user->save();

        Sanctum::actingAs($user->fresh(), ['*']);

        // Act: 取得用戶資料
        $response = $this->getJson('/api/v1/user');

        // Assert: 應包含 OAuth 狀態欄位
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'is_oauth_user',
                'can_set_password_without_current',
                'email_verified_at',
                'created_at',
                'updated_at',
            ])
            ->assertJson([
                'is_oauth_user' => true,
                'can_set_password_without_current' => false,
            ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmailCaseInsensitiveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_is_stored_as_lowercase()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'Test@Example.COM',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(201);

        // 驗證資料庫中的 email 是小寫
        $user = User::first();
        $this->assertEquals('test@example.com', $user->email);
    }

    /** @test */
    public function cannot_register_with_same_email_different_case()
    {
        // 先註冊一個用戶
        User::create([
            'name' => 'First User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // 嘗試用不同大小寫的相同 email 註冊
        $userData = [
            'name' => 'Second User',
            'email' => 'TEST@EXAMPLE.COM',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);

        // 確認只有一個用戶
        $this->assertEquals(1, User::count());
    }

    /** @test */
    public function can_login_with_different_case_email()
    {
        // 創建一個用戶
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        // 用大寫的 email 登入
        $loginData = [
            'email' => 'TEST@EXAMPLE.COM',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token'
        ]);

        // 驗證返回的用戶 ID 正確
        $this->assertEquals($user->id, $response->json('user.id'));
    }

    /** @test */
    public function mixed_case_emails_in_various_combinations()
    {
        $testCases = [
            'test@example.com',
            'Test@Example.Com',
            'TEST@EXAMPLE.COM',
            'tEsT@eXaMpLe.CoM'
        ];

        // 註冊第一個
        $userData = [
            'name' => 'Test User',
            'email' => $testCases[0],
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->postJson('/api/v1/register', $userData);
        $response->assertStatus(201);

        // 嘗試用其他大小寫組合註冊，都應該失敗
        foreach (array_slice($testCases, 1) as $index => $email) {
            $userData = [
                'name' => "Test User " . ($index + 2),
                'email' => $email,
                'password' => 'password123',
                'password_confirmation' => 'password123'
            ];

            $response = $this->postJson('/api/v1/register', $userData);
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['email']);
        }

        // 確認只有一個用戶
        $this->assertEquals(1, User::count());

        // 確認存儲的是小寫
        $this->assertEquals('test@example.com', User::first()->email);
    }

    /** @test */
    public function user_model_mutator_converts_email_to_lowercase()
    {
        $user = new User();
        $user->email = 'TEST@EXAMPLE.COM';

        $this->assertEquals('test@example.com', $user->email);
    }
}

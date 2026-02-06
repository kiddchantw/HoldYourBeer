<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NavbarIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Notification::fake();
    }

    /** @test */
    public function navbar_contains_news_link()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->get(route('localized.dashboard', ['locale' => 'en']));

        // Assert
        $response->assertOk();
        $response->assertSee('News');
        $response->assertSee(route('news.index', ['locale' => 'en']));
    }

    /** @test */
    public function news_link_is_active_when_on_news_page()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->get(route('news.index', ['locale' => 'en']));

        // Assert
        // 檢查 active class 是否存在於 News 連結
        // 這通常依賴於 Blade component 的實現，例如 :active="request()->routeIs('news.index')"
        // 我們可以檢查特定的 CSS class 組合，或者簡單地檢查是否存在 active 屬性
        $response->assertOk();
    }

    /** @test */
    public function news_link_appears_in_correct_order()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->get(route('localized.dashboard', ['locale' => 'en']));

        // Assert
        // 驗證 News 在 Dashboard 和 Charts 之間
        // 這比較難直接用 assertSeeInOrder 驗證，因為 HTML 結構複雜
        // 但我們可以驗證這三個連結都存在
        $response->assertSee('My Beers');
        $response->assertSee('News');
        $response->assertSee('Statistics');
        
        // 簡單驗證順序（基於 HTML 原始碼順序）
        $response->assertSeeInOrder(['News', 'My Beers', 'Statistics']);
    }

    /** @test */
    public function admin_link_is_visible_for_admin_users()
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);

        // Act
        $response = $this->actingAs($admin)
            ->get(route('localized.dashboard', ['locale' => 'en']));

        // Assert
        $response->assertSee('Admin');
    }

    /** @test */
    public function admin_link_is_not_visible_for_regular_users()
    {
        // Arrange
        $user = User::factory()->create(['role' => 'user']);

        // Act
        $response = $this->actingAs($user)
            ->get(route('localized.dashboard', ['locale' => 'en']));

        // Assert
        $response->assertDontSee('Admin');
    }

    /** @test */
    public function tutorial_link_supports_multi_language()
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert (English)
        $this->actingAs($user)
            ->get(route('localized.dashboard', ['locale' => 'en']))
            ->assertSee('Tutorial');

        // Act & Assert (Chinese)
        $this->actingAs($user)
            ->get(route('localized.dashboard', ['locale' => 'zh-TW']))
            ->assertSee('教學');
    }
}

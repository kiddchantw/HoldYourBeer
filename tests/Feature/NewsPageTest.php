<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Beer;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Notification::fake();
    }

    /** @test */
    public function authenticated_user_can_access_news_page()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        // 使用 localized route
        $response = $this->actingAs($user)
            ->get(route('news.index', ['locale' => 'en']));

        // Assert
        $response->assertOk();
        $response->assertViewIs('news.index');
        $response->assertViewHas('recentBeers');
    }

    /** @test */
    public function guest_cannot_access_news_page()
    {
        // Act
        // 使用 localized route，未登入應該被導向登入頁面
        $response = $this->get(route('news.index', ['locale' => 'en']));

        // Assert
        // 驗證導向到 localized login 頁面
        $response->assertRedirect(route('localized.login', ['locale' => 'en']));
    }
    /** @test */
    public function news_page_displays_recent_beers_with_brand_info()
    {
        // Arrange
        $user = User::factory()->create();
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $beer = Beer::factory()->create([
            'name' => 'Test Beer',
            'brand_id' => $brand->id,
            'created_at' => now()
        ]);

        // Act
        $response = $this->actingAs($user)
            ->get(route('news.index', ['locale' => 'en']));

        // Assert
        $response->assertOk();
        $response->assertSee('Test Beer');
        $response->assertSee('Test Brand');
        $response->assertSee('Recently Added Beers');
        $response->assertSee('System Updates');
    }

    /** @test */
    public function news_page_shows_empty_state_when_no_beers_exist()
    {
        // Arrange
        $user = User::factory()->create();
        // 不建立任何啤酒資料

        // Act
        $response = $this->actingAs($user)
            ->get(route('news.index', ['locale' => 'en']));

        // Assert
        $response->assertOk();
        $response->assertSee('No beers have been added yet.');
    }

    /** @test */
    public function news_page_limits_display_to_10_beers()
    {
        // Arrange
        $user = User::factory()->create();
        Beer::factory()->count(15)->create([
            'created_at' => now()->subDays(1)
        ]);

        // Act
        $response = $this->actingAs($user)
            ->get(route('news.index', ['locale' => 'en']));

        // Assert
        $response->assertOk();
        $beers = $response->viewData('recentBeers');
        $this->assertCount(10, $beers);
    }
}

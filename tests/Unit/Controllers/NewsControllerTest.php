<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use App\Models\Beer;
// use App\Models\Brand; // Will be used later
// use App\Http\Controllers\NewsController; // Will be created later
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewsControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_view_with_recent_beers()
    {
        // Arrange: 建立測試資料
        $beer1 = Beer::factory()->create([
            'created_at' => now()->subDays(5)
        ]);
        $beer2 = Beer::factory()->create([
            'created_at' => now()->subDays(10)
        ]);

        // Act: 執行 Controller 方法
        // 由於 NewsController 還沒建立，這裡會報錯，這是預期的 Red 狀態
        $controller = new \App\Http\Controllers\NewsController();
        $response = $controller->index(new Request());

        // Assert: 驗證結果
        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('news.index', $response->name());
        
        $recentBeers = $response->getData()['recentBeers'];
    }

    /** @test */
    public function it_limits_results_to_10_beers()
    {
        // Arrange: 建立 15 筆資料
        Beer::factory()->count(15)->create([
            'created_at' => now()->subDays(1)
        ]);

        // Act
        $controller = new \App\Http\Controllers\NewsController();
        $response = $controller->index(new Request());

        // Assert: 只返回 10 筆
        $recentBeers = $response->getData()['recentBeers'];
        $this->assertCount(10, $recentBeers);
    }

    /** @test */
    public function it_orders_beers_by_created_at_desc()
    {
        // Arrange: 建立不同時間的啤酒
        $beer1 = Beer::factory()->create(['created_at' => now()->subDays(10)]);
        $beer2 = Beer::factory()->create(['created_at' => now()->subDays(5)]);
        $beer3 = Beer::factory()->create(['created_at' => now()->subDays(1)]);

        // Act
        $controller = new \App\Http\Controllers\NewsController();
        $response = $controller->index(new Request());

        // Assert: 驗證順序（最新的在前）
        $recentBeers = $response->getData()['recentBeers'];
        $this->assertEquals($beer3->id, $recentBeers[0]->id);
        $this->assertEquals($beer2->id, $recentBeers[1]->id);
        $this->assertEquals($beer1->id, $recentBeers[2]->id);
    }

    /** @test */
    public function it_eager_loads_brand_relationship()
    {
        // Arrange
        Beer::factory()->count(3)->create([
            'created_at' => now()->subDays(1)
        ]);

        // Act
        $controller = new \App\Http\Controllers\NewsController();
        $response = $controller->index(new Request());

        // Assert: 驗證 Brand 已被 Eager Load
        $recentBeers = $response->getData()['recentBeers'];
        $this->assertTrue($recentBeers->first()->relationLoaded('brand'));
    }

    /** @test */
    public function it_returns_empty_collection_when_no_beers_exist()
    {
        // Arrange: 不建立任何資料

        // Act
        $controller = new \App\Http\Controllers\NewsController();
        $response = $controller->index(new Request());

        // Assert
        $recentBeers = $response->getData()['recentBeers'];
        $this->assertCount(0, $recentBeers);
    }
}


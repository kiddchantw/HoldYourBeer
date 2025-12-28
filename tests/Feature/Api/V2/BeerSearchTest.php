<?php

namespace Tests\Feature\Api\V2;

use App\Models\Beer;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class BeerSearchTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $brand;
    private $beer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->brand = Brand::factory()->create(['name' => 'Suntory']);
        $this->beer = Beer::factory()->create([
            'name' => 'Suntory Premium Malts',
            'brand_id' => $this->brand->id,
            'style' => 'Pilsner'
        ]);
        
        // Create another beer from a different brand
        $otherBrand = Brand::factory()->create(['name' => 'Kirin']);
        Beer::factory()->create([
            'name' => 'Kirin Ichiban',
            'brand_id' => $otherBrand->id,
            'style' => 'Lager'
        ]);
    }

    /** @test */
    public function it_can_search_beers_by_name()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson(route('v2.beers.search', ['search' => 'Premium']));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Suntory Premium Malts');
    }

    /** @test */
    public function it_can_filter_beers_by_brand_id()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson(route('v2.beers.search', ['brand_id' => $this->brand->id]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Suntory Premium Malts');
    }

    /** @test */
    public function it_returns_empty_if_no_match()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson(route('v2.beers.search', ['search' => 'NonExistentBeer']));

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function it_is_protected_by_authentication()
    {
        $response = $this->getJson(route('v2.beers.search'));

        $response->assertStatus(401);
    }
    
    /** @test */
    public function it_limits_results()
    {
        Sanctum::actingAs($this->user);
        
        // Create 5 beers for the same brand
        Beer::factory()->count(5)->create(['brand_id' => $this->brand->id]);
        
        // Request with limit 2
        $response = $this->getJson(route('v2.beers.search', [
            'brand_id' => $this->brand->id,
            'limit' => 2
        ]));
        
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}

<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Brand;
use App\Models\Beer;
use App\Models\UserBeerCount;
use App\Models\TastingLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @covers \spec\api\api.yaml
 * 
 * API Endpoints covered:
 * - GET /api/beers - List user's tracked beers
 * - POST /api/beers/{id}/count_actions - Increment/decrement tasting count
 * - GET /api/beers/{id}/tasting_logs - View tasting history
 * 
 * Test coverage:
 * - Beer list retrieval with sorting and filtering
 * - Tasting count modification with proper locking
 * - Tasting log creation and retrieval
 * - Authentication requirements
 * - Error handling for edge cases
 */
class BeerEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Brand $brand;
    private Beer $beer;
    private UserBeerCount $userBeerCount;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->brand = Brand::factory()->create(['name' => 'Test Brand']);
        $this->beer = Beer::factory()->create([
            'name' => 'Test Beer',
            'brand_id' => $this->brand->id,
            'style' => 'IPA'
        ]);
        $this->userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $this->user->id,
            'beer_id' => $this->beer->id,
            'count' => 3
        ]);
    }

    #[Test]
    public function it_requires_authentication_for_beer_endpoints()
    {
        // GET /api/beers
        $response = $this->getJson('/api/beers');
        $response->assertStatus(401);

        // POST /api/beers/{id}/count_actions
        $response = $this->postJson("/api/beers/{$this->beer->id}/count_actions", [
            'action' => 'increment'
        ]);
        $response->assertStatus(401);

        // GET /api/beers/{id}/tasting_logs
        $response = $this->getJson("/api/beers/{$this->beer->id}/tasting_logs");
        $response->assertStatus(401);
    }

    #[Test]
    public function it_can_get_user_tracked_beers()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/beers');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'style',
                        'brand' => [
                            'id',
                            'name'
                        ],
                        'tasting_count',
                        'last_tasted_at'
                    ]
                ])
                ->assertJsonFragment([
                    'id' => $this->beer->id,
                    'name' => 'Test Beer',
                    'style' => 'IPA',
                    'brand' => [
                        'id' => $this->brand->id,
                        'name' => 'Test Brand'
                    ],
                    'tasting_count' => 3
                ]);
    }

    #[Test]
    public function it_can_filter_beers_by_brand()
    {
        Sanctum::actingAs($this->user);

        // Create another brand and beer
        $anotherBrand = Brand::factory()->create(['name' => 'Another Brand']);
        $anotherBeer = Beer::factory()->create([
            'name' => 'Another Beer',
            'brand_id' => $anotherBrand->id
        ]);
        UserBeerCount::factory()->create([
            'user_id' => $this->user->id,
            'beer_id' => $anotherBeer->id,
            'count' => 1
        ]);

        // Filter by first brand
        $response = $this->getJson("/api/beers?brand_id={$this->brand->id}");
        
        $response->assertStatus(200)
                ->assertJsonCount(1)
                ->assertJsonFragment([
                    'name' => 'Test Beer'
                ]);
    }

    #[Test]
    public function it_can_increment_beer_count()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/beers/{$this->beer->id}/count_actions", [
            'action' => 'increment'
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'tasting_count' => 4
                ]);

        // Check database
        $this->assertDatabaseHas('user_beer_counts', [
            'id' => $this->userBeerCount->id,
            'count' => 4
        ]);

        // Check tasting log was created
        $this->assertDatabaseHas('tasting_logs', [
            'user_beer_count_id' => $this->userBeerCount->id,
            'action' => 'increment'
        ]);
    }

    #[Test]
    public function it_can_decrement_beer_count()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/beers/{$this->beer->id}/count_actions", [
            'action' => 'decrement'
        ]);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'tasting_count' => 2
                ]);

        // Check database
        $this->assertDatabaseHas('user_beer_counts', [
            'id' => $this->userBeerCount->id,
            'count' => 2
        ]);

        // Check tasting log was created
        $this->assertDatabaseHas('tasting_logs', [
            'user_beer_count_id' => $this->userBeerCount->id,
            'action' => 'decrement'
        ]);
    }

    #[Test]
    public function it_cannot_decrement_below_zero()
    {
        Sanctum::actingAs($this->user);

        // Set count to 0
        $this->userBeerCount->update(['count' => 0]);

        $response = $this->postJson("/api/beers/{$this->beer->id}/count_actions", [
            'action' => 'decrement'
        ]);

        $response->assertStatus(400)
                ->assertJsonFragment([
                    'error' => 'Cannot decrement count below zero.'
                ]);

        // Check count remained 0
        $this->assertDatabaseHas('user_beer_counts', [
            'id' => $this->userBeerCount->id,
            'count' => 0
        ]);
    }

    #[Test]
    public function it_returns_404_for_untracked_beer()
    {
        Sanctum::actingAs($this->user);

        // Create a beer not tracked by user
        $untrackedBeer = Beer::factory()->create([
            'brand_id' => $this->brand->id
        ]);

        $response = $this->postJson("/api/beers/{$untrackedBeer->id}/count_actions", [
            'action' => 'increment'
        ]);

        $response->assertStatus(404)
                ->assertJsonFragment([
                    'error' => 'Beer not found in your tracked list.'
                ]);
    }

    #[Test]
    public function it_validates_count_action_input()
    {
        Sanctum::actingAs($this->user);

        // Missing action
        $response = $this->postJson("/api/beers/{$this->beer->id}/count_actions", []);
        $response->assertStatus(422);

        // Invalid action
        $response = $this->postJson("/api/beers/{$this->beer->id}/count_actions", [
            'action' => 'invalid'
        ]);
        $response->assertStatus(422);
    }

    #[Test]
    public function it_can_get_tasting_logs()
    {
        Sanctum::actingAs($this->user);

        // Create some tasting logs
        $log1 = TastingLog::factory()->create([
            'user_beer_count_id' => $this->userBeerCount->id,
            'action' => 'increment',
            'note' => 'Great taste!'
        ]);

        $log2 = TastingLog::factory()->create([
            'user_beer_count_id' => $this->userBeerCount->id,
            'action' => 'decrement',
            'note' => null
        ]);

        $response = $this->getJson("/api/beers/{$this->beer->id}/tasting_logs");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'action',
                        'tasted_at',
                        'note'
                    ]
                ])
                ->assertJsonCount(2);
    }

    #[Test]
    public function it_returns_404_for_tasting_logs_of_untracked_beer()
    {
        Sanctum::actingAs($this->user);

        // Create a beer not tracked by user
        $untrackedBeer = Beer::factory()->create([
            'brand_id' => $this->brand->id
        ]);

        $response = $this->getJson("/api/beers/{$untrackedBeer->id}/tasting_logs");

        $response->assertStatus(404)
                ->assertJsonFragment([
                    'error' => 'Beer not found in your tracked list.'
                ]);
    }
}
<?php

namespace Tests\Feature\Api\V1;

use App\Models\Beer;
use App\Models\Brand;
use App\Models\User;
use App\Models\UserBeerCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test Beer authorization policies to ensure users can only
 * access and modify their own tracked beers.
 */
class BeerAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Brand $brand;
    protected Beer $beer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create two users
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        // Create a brand and beer
        $this->brand = Brand::factory()->create();
        $this->beer = Beer::factory()->create(['brand_id' => $this->brand->id]);
    }

    /** @test */
    public function user_cannot_update_count_for_beer_they_are_not_tracking(): void
    {
        // User tracks the beer
        UserBeerCount::create([
            'user_id' => $this->user->id,
            'beer_id' => $this->beer->id,
            'count' => 1,
            'last_tasted_at' => now(),
        ]);

        // Other user tries to increment the count
        $response = $this->actingAs($this->otherUser, 'sanctum')
            ->postJson("/api/v1/beers/{$this->beer->id}/count_actions", [
                'action' => 'increment',
            ]);

        // Should be forbidden (403)
        $response->assertStatus(403);
        $response->assertJsonStructure([
            'error_code',
            'message',
        ]);
        $response->assertJsonPath('error_code', 'AUTH_002');
    }

    /** @test */
    public function user_can_update_count_for_beer_they_are_tracking(): void
    {
        // User tracks the beer
        UserBeerCount::create([
            'user_id' => $this->user->id,
            'beer_id' => $this->beer->id,
            'count' => 1,
            'last_tasted_at' => now(),
        ]);

        // User increments their own beer count
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/beers/{$this->beer->id}/count_actions", [
                'action' => 'increment',
            ]);

        // Should succeed
        $response->assertStatus(200);
        $response->assertJsonPath('data.tasting_count', 2);
    }

    /** @test */
    public function user_cannot_view_tasting_logs_for_beer_they_are_not_tracking(): void
    {
        // User tracks the beer
        UserBeerCount::create([
            'user_id' => $this->user->id,
            'beer_id' => $this->beer->id,
            'count' => 1,
            'last_tasted_at' => now(),
        ]);

        // Other user tries to view tasting logs
        $response = $this->actingAs($this->otherUser, 'sanctum')
            ->getJson("/api/v1/beers/{$this->beer->id}/tasting_logs");

        // Should be forbidden (403)
        $response->assertStatus(403);
        $response->assertJsonStructure([
            'error_code',
            'message',
        ]);
        $response->assertJsonPath('error_code', 'AUTH_002');
    }

    /** @test */
    public function user_can_view_tasting_logs_for_beer_they_are_tracking(): void
    {
        // User tracks the beer
        $userBeerCount = UserBeerCount::create([
            'user_id' => $this->user->id,
            'beer_id' => $this->beer->id,
            'count' => 1,
            'last_tasted_at' => now(),
        ]);

        // Create a tasting log
        \App\Models\TastingLog::create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'initial',
            'tasted_at' => now(),
            'note' => 'First taste',
        ]);

        // User views their own tasting logs
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/beers/{$this->beer->id}/tasting_logs");

        // Should succeed
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.note', 'First taste');
    }

    /** @test */
    public function unauthenticated_user_cannot_access_beer_endpoints(): void
    {
        // Try to increment count without authentication
        $response = $this->postJson("/api/v1/beers/{$this->beer->id}/count_actions", [
            'action' => 'increment',
        ]);

        $response->assertStatus(401);
        $response->assertJsonStructure([
            'error_code',
            'message',
        ]);
        $response->assertJsonPath('error_code', 'AUTH_001');
    }

    /** @test */
    public function user_cannot_decrement_count_below_zero(): void
    {
        // User tracks the beer with count = 0
        UserBeerCount::create([
            'user_id' => $this->user->id,
            'beer_id' => $this->beer->id,
            'count' => 0,
            'last_tasted_at' => now(),
        ]);

        // Try to decrement
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/beers/{$this->beer->id}/count_actions", [
                'action' => 'decrement',
            ]);

        // Should fail with business logic error
        $response->assertStatus(400);
        $response->assertJson([
            'error_code' => 'BIZ_001',
            'message' => 'Cannot decrement count below zero.',
        ]);
    }

    /** @test */
    public function accessing_non_existent_beer_returns_404(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/beers/99999/tasting_logs');

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'error_code',
            'message',
        ]);
        $response->assertJsonPath('error_code', 'RES_001');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserBeerCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Related specifications: spec/features/beer_tracking/managing_tastings.feature
 *
 * Scenarios covered:
 * - Incrementing the count of an existing beer
 * - Correcting a mistaken increment by decrementing
 *
 * Test coverage:
 * - Tasting count increment functionality
 * - Tasting count decrement functionality
 * - Tasting log creation for tracking changes
 * - Preventing decrement below zero
 * - Concurrent operation handling with proper locking
 */
class TastingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_increments_the_tasting_count_and_creates_a_log()
    {
        $user = User::factory()->create();
        $userBeerCount = UserBeerCount::factory()->create(['user_id' => $user->id, 'count' => 1]);

        $response = $this->actingAs($user)->post(route('tasting.increment', ['locale' => 'en', 'id' => $userBeerCount->id]));

        $response->assertRedirect();
        $this->assertDatabaseHas('user_beer_counts', ['id' => $userBeerCount->id, 'count' => 2]);
        $this->assertDatabaseHas('tasting_logs', ['user_beer_count_id' => $userBeerCount->id, 'action' => 'increment']);
    }

    public function test_it_decrements_the_tasting_count_and_creates_a_log()
    {
        $user = User::factory()->create();
        $userBeerCount = UserBeerCount::factory()->create(['user_id' => $user->id, 'count' => 2]);

        $response = $this->actingAs($user)->post(route('tasting.decrement', ['locale' => 'en', 'id' => $userBeerCount->id]));

        $response->assertRedirect();
        $this->assertDatabaseHas('user_beer_counts', ['id' => $userBeerCount->id, 'count' => 1]);
        $this->assertDatabaseHas('tasting_logs', ['user_beer_count_id' => $userBeerCount->id, 'action' => 'decrement']);
    }

    public function test_it_does_not_decrement_below_zero()
    {
        $user = User::factory()->create();
        $userBeerCount = UserBeerCount::factory()->create(['user_id' => $user->id, 'count' => 0]);

        $response = $this->actingAs($user)->post(route('tasting.decrement', ['locale' => 'en', 'id' => $userBeerCount->id]));

        $response->assertRedirect();
        $this->assertDatabaseHas('user_beer_counts', ['id' => $userBeerCount->id, 'count' => 0]);
        $this->assertDatabaseMissing('tasting_logs', ['user_beer_count_id' => $userBeerCount->id, 'action' => 'decrement']);
    }

    public function test_concurrent_increments_are_handled_correctly()
    {
        $user = User::factory()->create();
        $userBeerCount = UserBeerCount::factory()->create(['user_id' => $user->id, 'count' => 1]);

        // Simulate concurrent requests by running multiple increments
        $responses = [];
        for ($i = 0; $i < 3; $i++) {
            $responses[] = $this->actingAs($user)->post(route('tasting.increment', ['locale' => 'en', 'id' => $userBeerCount->id]));
        }

        // All responses should be successful
        foreach ($responses as $response) {
            $response->assertRedirect();
        }

        // Final count should be exactly 4 (1 + 3 increments)
        $this->assertDatabaseHas('user_beer_counts', ['id' => $userBeerCount->id, 'count' => 4]);

        // Should have exactly 3 increment logs
        $this->assertDatabaseCount('tasting_logs', 3);
        $this->assertDatabaseHas('tasting_logs', ['user_beer_count_id' => $userBeerCount->id, 'action' => 'increment']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserBeerCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TastingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_increments_the_tasting_count_and_creates_a_log()
    {
        $user = User::factory()->create();
        $userBeerCount = UserBeerCount::factory()->create(['user_id' => $user->id, 'count' => 1]);

        $response = $this->actingAs($user)->post(route('tasting.increment.fallback', $userBeerCount));

        $response->assertRedirect();
        $this->assertDatabaseHas('user_beer_counts', ['id' => $userBeerCount->id, 'count' => 2]);
        $this->assertDatabaseHas('tasting_logs', ['user_beer_count_id' => $userBeerCount->id, 'action' => 'increment']);
    }

    public function test_it_decrements_the_tasting_count_and_creates_a_log()
    {
        $user = User::factory()->create();
        $userBeerCount = UserBeerCount::factory()->create(['user_id' => $user->id, 'count' => 2]);

        $response = $this->actingAs($user)->post(route('tasting.decrement.fallback', $userBeerCount));

        $response->assertRedirect();
        $this->assertDatabaseHas('user_beer_counts', ['id' => $userBeerCount->id, 'count' => 1]);
        $this->assertDatabaseHas('tasting_logs', ['user_beer_count_id' => $userBeerCount->id, 'action' => 'decrement']);
    }

    public function test_it_does_not_decrement_below_zero()
    {
        $user = User::factory()->create();
        $userBeerCount = UserBeerCount::factory()->create(['user_id' => $user->id, 'count' => 0]);

        $response = $this->actingAs($user)->post(route('tasting.decrement.fallback', $userBeerCount));

        $response->assertRedirect();
        $this->assertDatabaseHas('user_beer_counts', ['id' => $userBeerCount->id, 'count' => 0]);
        $this->assertDatabaseMissing('tasting_logs', ['user_beer_count_id' => $userBeerCount->id, 'action' => 'decrement']);
    }
}

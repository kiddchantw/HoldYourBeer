<?php

namespace Tests\Feature;

use App\Models\Beer;
use App\Models\Brand;
use App\Models\TastingLog;
use App\Models\User;
use App\Models\UserBeerCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewingTastingHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_view_the_tasting_history_for_a_beer()
    {
        // Arrange: Create a user, brand, beer, and user beer count
        $user = User::factory()->create();
        $brand = Brand::factory()->create(['name' => 'Kirin']);
        $beer = Beer::factory()->create(['name' => 'Lager', 'brand_id' => $brand->id]);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        // Arrange: Create tasting logs
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'initial',
            'note' => 'A crisp and refreshing lager, perfect for a summer day.',
            'tasted_at' => '2025-08-20 10:00:00',
        ]);

        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'increment',
            'note' => null,
            'tasted_at' => '2025-08-21 18:30:00',
        ]);

        // Act: Authenticate as the user and visit the tasting history page
        $response = $this->actingAs($user)->get(route('beers.history.fallback', ['beer' => $beer->id]));

        // Assert: The response is successful
        $response->assertStatus(200);

        // Assert: The view contains the correct beer name
        $response->assertSee('Kirin Lager');

        // Assert: The view contains the tasting history entries
        $response->assertSee('initial');
        $response->assertSee('A crisp and refreshing lager, perfect for a summer day.');
        $response->assertSee('August 20, 2025');

        $response->assertSee('increment');
        $response->assertSee('August 21, 2025');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Beer;
use App\Models\Brand;
use App\Models\TastingLog;
use App\Models\User;
use App\Models\UserBeerCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Related specifications: spec/features/beer_tracking/viewing_tasting_history.feature
 *
 * Scenarios covered:
 * - Viewing tasting history for a specific beer
 * - Displaying tasting notes and timestamps
 * - Showing increment actions in history
 *
 * Test coverage:
 * - Tasting history page access
 * - Beer information display
 * - Tasting log entries display
 * - Date formatting
 * - User authentication requirements
 */
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

        // Arrange: Create tasting logs (using UTC timezone as configured in config/app.php)
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'initial',
            'note' => 'A crisp and refreshing lager, perfect for a summer day.',
            'tasted_at' => '2025-08-20 02:00:00', // UTC time that converts to Aug 20, 2025 in Asia/Taipei
        ]);

        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'increment',
            'note' => null,
            'tasted_at' => '2025-08-21 02:00:00', // UTC time that converts to Aug 21, 2025 in Asia/Taipei
        ]);

        // Act: Authenticate as the user and visit the tasting history page
        $response = $this->actingAs($user)->get(route('beers.history', ['locale' => 'en', 'beerId' => $beer->id]));

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

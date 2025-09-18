<?php

namespace Tests\Unit\Models;

use App\Models\Beer;
use App\Models\Brand;
use App\Models\User;
use App\Models\UserBeerCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Related specifications: spec/features/beer_tracking/adding_a_beer.feature
 * Related specifications: spec/features/beer_tracking/viewing_the_list.feature
 * Related specifications: spec/features/beer_tracking/managing_tastings.feature
 *
 * Scenarios covered:
 * - Beer model relationships and data integrity
 * - Beer-brand associations
 * - User tasting count calculations
 * - Beer collection management
 *
 * Test coverage:
 * - Beer-Brand relationship validation
 * - Beer-UserBeerCount relationship
 * - Tasting count retrieval for users
 * - Model association integrity
 */
class BeerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_beer_belongs_to_a_brand()
    {
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);

        $this->assertInstanceOf(Brand::class, $beer->brand);
        $this->assertEquals($brand->id, $beer->brand->id);
    }

    #[Test]
    public function a_beer_has_many_user_beer_counts()
    {
        $beer = Beer::factory()->create();
        UserBeerCount::factory()->count(3)->create(['beer_id' => $beer->id]);

        $this->assertCount(3, $beer->userBeerCounts);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $beer->userBeerCounts);
        $this->assertInstanceOf(UserBeerCount::class, $beer->userBeerCounts->first());
    }

    #[Test]
    public function it_returns_correct_tasting_count_for_a_user()
    {
        $user = User::factory()->create();
        $beer = Beer::factory()->create();
        UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 5,
        ]);

        $this->assertEquals(5, $beer->getTastingCountForUser($user->id));
    }

    #[Test]
    public function it_returns_zero_when_user_has_no_tasting_count()
    {
        $user = User::factory()->create();
        $beer = Beer::factory()->create();

        $this->assertEquals(0, $beer->getTastingCountForUser($user->id));
    }
}

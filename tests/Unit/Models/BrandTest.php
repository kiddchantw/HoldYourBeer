<?php

namespace Tests\Unit\Models;

use App\Models\Beer;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test coverage for Brand model functionality
 *
 * Related specifications:
 * - spec/features/beer_tracking/adding_a_beer.feature
 * - spec/features/brand_analytics_charts.feature
 *
 * Scenarios covered:
 * - Brand model relationships and data integrity
 * - Brand-beer associations
 * - Brand collection management
 *
 * Test coverage:
 * - Brand-Beer relationship validation
 * - Brand model association integrity
 * - Collection handling for brand-related beers
 */
class BrandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_brand_has_many_beers()
    {
        $brand = Brand::factory()->create();
        Beer::factory()->count(2)->create(['brand_id' => $brand->id]);

        $this->assertCount(2, $brand->beers);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $brand->beers);
        $this->assertInstanceOf(Beer::class, $brand->beers->first());
    }
}

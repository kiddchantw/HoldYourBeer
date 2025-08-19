<?php

namespace Tests\Unit\Models;

use App\Models\Beer;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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

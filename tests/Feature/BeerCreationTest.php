<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BeerCreationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->brand = Brand::factory()->create();
    }

    #[Test]
    public function a_user_can_successfully_add_a_beer(): void
    {
        $beerData = [
            'name' => 'Test IPA',
            'brand_id' => $this->brand->id,
            'style' => 'IPA',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/beers', $beerData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('beers', $beerData);
    }

    #[Test]
    public function it_returns_a_validation_error_if_name_is_missing(): void
    {
        $beerData = [
            // name is intentionally missing
            'brand_id' => $this->brand->id,
            'style' => 'IPA',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/beers', $beerData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function it_returns_an_unauthorized_error_if_user_is_not_authenticated(): void
    {
        $beerData = [
            'name' => 'Test IPA',
            'brand_id' => $this->brand->id,
            'style' => 'IPA',
        ];

        $response = $this->postJson('/api/beers', $beerData);

        $response->assertStatus(401);
    }

    #[Test]
    public function it_returns_a_validation_error_if_brand_id_does_not_exist(): void
    {
        $beerData = [
            'name' => 'Another Beer',
            'brand_id' => 999, // Non-existent brand_id
            'style' => 'Stout',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/beers', $beerData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['brand_id']);
    }

    #[Test]
    public function it_sets_the_tasting_count_to_1_when_a_new_beer_is_added(): void
    {
        $beerData = [
            'name' => 'Test IPA',
            'brand_id' => $this->brand->id,
            'style' => 'IPA',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/beers', $beerData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('user_beer_counts', [
            'user_id' => $this->user->id,
            'beer_id' => $response->json('id'),
            'count' => 1,
        ]);
    }
}
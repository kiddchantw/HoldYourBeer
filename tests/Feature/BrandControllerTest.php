<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Related specifications: spec/api/api.yaml
 *
 * Scenarios covered:
 * - Get all brands endpoint
 * - Authentication requirements for brand access
 * - Brand listing with proper ordering
 *
 * Test coverage:
 * - GET /api/brands endpoint functionality
 * - Bearer token authentication validation
 * - Brand data structure and ordering
 * - Unauthorized access handling
 */
class BrandControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated users can get all brands.
     */
    public function test_authenticated_user_can_get_all_brands(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create some test brands
        $brands = [
            Brand::create(['name' => 'Guinness']),
            Brand::create(['name' => 'Asahi']),
            Brand::create(['name' => 'Brewdog']),
        ];

        // Make the API request
        $response = $this->getJson('/api/brands');

        // Assert successful response
        $response->assertStatus(200);

        // Assert brands are returned in alphabetical order by name
        $response->assertJsonCount(3);
        $response->assertJson([
            ['id' => $brands[1]->id, 'name' => 'Asahi'],      // First alphabetically
            ['id' => $brands[2]->id, 'name' => 'Brewdog'],    // Second alphabetically
            ['id' => $brands[0]->id, 'name' => 'Guinness'],   // Third alphabetically
        ]);
    }

    /**
     * Test that unauthenticated users cannot access brands endpoint.
     */
    public function test_unauthenticated_user_cannot_get_brands(): void
    {
        // Create some test brands
        Brand::create(['name' => 'Guinness']);
        Brand::create(['name' => 'Brewdog']);

        // Make the API request without authentication
        $response = $this->getJson('/api/brands');

        // Assert unauthorized response
        $response->assertStatus(401);
    }

    /**
     * Test that the endpoint returns empty array when no brands exist.
     */
    public function test_returns_empty_array_when_no_brands_exist(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Make the API request with no brands in database
        $response = $this->getJson('/api/brands');

        // Assert successful response with empty array
        $response->assertStatus(200);
        $response->assertJsonCount(0);
        $response->assertExactJson([]);
    }

    /**
     * Test that brands are returned with correct structure.
     */
    public function test_brands_have_correct_json_structure(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a test brand
        $brand = Brand::create(['name' => 'Test Brand']);

        // Make the API request
        $response = $this->getJson('/api/brands');

        // Assert response structure
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'created_at',
                'updated_at',
            ]
        ]);

        // Assert specific brand data
        $response->assertJsonFragment([
            'id' => $brand->id,
            'name' => 'Test Brand',
        ]);
    }
}
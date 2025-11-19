<?php

namespace Tests\Feature\Api\V1;

use App\Exceptions\BusinessLogicException;
use App\Models\Beer;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test unified API exception handling to ensure:
 * 1. Sensitive information is not leaked in error messages
 * 2. Consistent error response format across all API endpoints
 * 3. Proper HTTP status codes are returned
 */
class ExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function validation_exception_returns_consistent_format(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/beers', [
                'name' => '', // Invalid: name is required
                'brand_id' => 999, // Invalid: brand does not exist
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'error_code',
            'message',
            'errors' => [
                'name',
                'brand_id',
            ],
        ]);
        $response->assertJson([
            'error_code' => 'VAL_001',
            'message' => 'The given data was invalid.',
        ]);
    }

    /** @test */
    public function authentication_exception_returns_401(): void
    {
        $response = $this->getJson('/api/v1/beers');

        $response->assertStatus(401);
        $response->assertJsonStructure([
            'error_code',
            'message',
        ]);
        $response->assertJsonPath('error_code', 'AUTH_001');
    }

    /** @test */
    public function authorization_exception_returns_403(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);

        // User tracks the beer
        \App\Models\UserBeerCount::create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 1,
            'last_tasted_at' => now(),
        ]);

        // Other user tries to access
        $response = $this->actingAs($otherUser, 'sanctum')
            ->postJson("/api/v1/beers/{$beer->id}/count_actions", [
                'action' => 'increment',
            ]);

        $response->assertStatus(403);
        $response->assertJsonStructure([
            'error_code',
            'message',
        ]);
        $response->assertJsonPath('error_code', 'AUTH_002');
    }

    /** @test */
    public function model_not_found_exception_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/beers/99999/tasting_logs');

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'error_code',
            'message',
        ]);
        $response->assertJsonPath('error_code', 'RES_001');
    }

    /** @test */
    public function business_logic_exception_returns_custom_error_code(): void
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);

        // User tracks the beer with count = 0
        \App\Models\UserBeerCount::create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 0,
            'last_tasted_at' => now(),
        ]);

        // Try to decrement below zero
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/beers/{$beer->id}/count_actions", [
                'action' => 'decrement',
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error_code' => 'BIZ_001',
            'message' => 'Cannot decrement count below zero.',
        ]);
    }

    /** @test */
    public function not_found_route_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/non-existent-endpoint');

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'error_code',
            'message',
        ]);
        $response->assertJsonPath('error_code', 'RES_001');
    }

    /** @test */
    public function error_response_does_not_leak_sensitive_information(): void
    {
        $user = User::factory()->create();

        // Force a validation error
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/beers', [
                'name' => 'Test Beer',
                'brand_id' => 999, // Non-existent brand
            ]);

        // Ensure response doesn't contain sensitive data
        $content = $response->getContent();

        // Should not contain database paths or internal class names
        $this->assertStringNotContainsString('database', strtolower($content));
        $this->assertStringNotContainsString('eloquent', strtolower($content));
        $this->assertStringNotContainsString('query', strtolower($content));

        // Should have proper error structure
        $response->assertJsonStructure([
            'error_code',
            'message',
        ]);
    }

    /** @test */
    public function business_logic_exception_can_be_thrown_with_custom_status_code(): void
    {
        // This test validates the BusinessLogicException class
        $exception = new BusinessLogicException(
            'Custom error message',
            'CUSTOM_001',
            422
        );

        $this->assertEquals('Custom error message', $exception->getMessage());
        $this->assertEquals('CUSTOM_001', $exception->getErrorCode());
        $this->assertEquals(422, $exception->getStatusCode());
    }

    /** @test */
    public function all_api_endpoints_return_json_errors(): void
    {
        // Test that API endpoints always return JSON, not HTML error pages

        // Test 404
        $response = $this->getJson('/api/v1/invalid-endpoint');
        $response->assertHeader('Content-Type', 'application/json');

        // Test 401
        $response = $this->getJson('/api/v1/beers');
        $response->assertHeader('Content-Type', 'application/json');

        // Test 422
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/beers', []);
        $response->assertHeader('Content-Type', 'application/json');
    }
}

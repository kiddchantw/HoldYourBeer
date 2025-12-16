<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBrandControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        // Create normal user
        $this->user = User::factory()->create(['role' => 'user']);

        // Disable CSRF middleware for testing
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_admin_can_view_brand_list()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.brands.index', ['locale' => 'en']));

        $response->assertStatus(200);
        $response->assertViewIs('admin.brands.index');
    }

    public function test_non_admin_cannot_view_brand_list()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.brands.index', ['locale' => 'en']));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_brand()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.brands.store', ['locale' => 'en']), [
                'name' => 'Test Brand'
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('brands', ['name' => 'Test Brand']);
    }

    public function test_admin_can_update_brand()
    {
        $brand = Brand::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.brands.update', ['locale' => 'en', 'brand' => $brand->id]), [
                'name' => 'Updated Brand Name'
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'Updated Brand Name'
        ]);
    }

    public function test_admin_can_delete_brand()
    {
        $brand = Brand::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.brands.destroy', ['locale' => 'en', 'brand' => $brand->id]));

        $response->assertRedirect();
        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    public function test_edit_page_loads_correctly_with_locale()
    {
        // This test specifically targets the issue with locale parameter in edit route
        $brand = Brand::factory()->create();

        // Route: /{locale}/admin/brands/{brand}/edit
        $response = $this->actingAs($this->admin)
            ->get(route('admin.brands.edit', ['locale' => 'en', 'brand' => $brand->id]));

        $response->assertStatus(200);
        $response->assertStatus(200);
        $response->assertViewHas('brand');
    }

    public function test_dashboard_with_array_tab_parameter()
    {
        // This test ensures that passing ?tab[]=something doesn't crash the page
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['locale' => 'en', 'tab' => ['brands']]));

        $response->assertStatus(200);
    }

    // ========== 2025-12-16: AJAX and Modal Edit Tests ==========

    public function test_update_with_json_request_returns_json_response()
    {
        $brand = Brand::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->json('PUT', route('admin.brands.update', ['locale' => 'en', 'brand' => $brand->id]), [
                'name' => 'New Name'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => __('brands.messages.updated'),
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'brand' => ['id', 'name']
        ]);

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'New Name'
        ]);
    }

    public function test_update_with_json_request_returns_validation_errors()
    {
        $brand1 = Brand::factory()->create(['name' => 'Existing Brand']);
        $brand2 = Brand::factory()->create(['name' => 'Brand to Update']);

        $response = $this->actingAs($this->admin)
            ->json('PUT', route('admin.brands.update', ['locale' => 'en', 'brand' => $brand2->id]), [
                'name' => 'Existing Brand' // Duplicate name
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_update_with_traditional_form_request_redirects()
    {
        $brand = Brand::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.brands.update', ['locale' => 'en', 'brand' => $brand->id]), [
                'name' => 'New Name'
            ]);

        $response->assertRedirect(route('admin.dashboard', ['locale' => 'en', 'tab' => 'brands']));
        $response->assertSessionHas('success', __('brands.messages.updated'));
    }

    // ========== 2025-12-16: Dashboard Sorting Tests ==========

    public function test_dashboard_brands_tab_supports_sorting_by_name()
    {
        Brand::factory()->create(['name' => 'Zebra Beer']);
        Brand::factory()->create(['name' => 'Alpha Beer']);
        Brand::factory()->create(['name' => 'Beta Beer']);

        // Test ascending order
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', [
                'locale' => 'en',
                'tab' => 'brands',
                'sort_by' => 'name',
                'sort_order' => 'asc'
            ]));

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Alpha Beer', 'Beta Beer', 'Zebra Beer']);

        // Test descending order
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', [
                'locale' => 'en',
                'tab' => 'brands',
                'sort_by' => 'name',
                'sort_order' => 'desc'
            ]));

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Zebra Beer', 'Beta Beer', 'Alpha Beer']);
    }

    public function test_dashboard_brands_tab_validates_sort_parameters()
    {
        Brand::factory()->create(['name' => 'Test Brand']);

        // Test with invalid sort_by parameter
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', [
                'locale' => 'en',
                'tab' => 'brands',
                'sort_by' => 'invalid_column', // Should be ignored
                'sort_order' => 'asc'
            ]));

        $response->assertStatus(200); // Should not crash

        // Test with SQL injection attempt
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', [
                'locale' => 'en',
                'tab' => 'brands',
                'sort_by' => 'name; DROP TABLE brands;--',
                'sort_order' => 'asc'
            ]));

        $response->assertStatus(200); // Should not crash
        $this->assertDatabaseHas('brands', ['name' => 'Test Brand']); // Table should still exist
    }

    public function test_dashboard_brands_tab_defaults_to_name_asc()
    {
        Brand::factory()->create(['name' => 'Zebra']);
        Brand::factory()->create(['name' => 'Alpha']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['locale' => 'en', 'tab' => 'brands']));

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Alpha', 'Zebra']); // Default: name ASC
    }

    // ========== 2025-12-16: Soft Delete Tests ==========

    public function test_destroy_soft_deletes_brand()
    {
        $brand = Brand::factory()->create(['name' => 'Test Brand']);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.brands.destroy', ['locale' => 'en', 'brand' => $brand->id]));

        $response->assertRedirect(route('admin.dashboard', ['locale' => 'en', 'tab' => 'brands']));
        $response->assertSessionHas('success', __('brands.messages.deleted'));

        // Brand should still exist in database but marked as deleted
        $this->assertDatabaseHas('brands', ['id' => $brand->id]);
        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }

    public function test_soft_deleted_brands_not_shown_in_default_list()
    {
        $activeBrand = Brand::factory()->create(['name' => 'Active Brand']);
        $deletedBrand = Brand::factory()->create(['name' => 'Deleted Brand']);
        $deletedBrand->delete();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['locale' => 'en', 'tab' => 'brands']));

        $response->assertStatus(200);
        $response->assertSee('Active Brand');
        $response->assertDontSee('Deleted Brand');
    }

    public function test_soft_deleted_brands_shown_with_show_deleted_parameter()
    {
        $activeBrand = Brand::factory()->create(['name' => 'Active Brand']);
        $deletedBrand = Brand::factory()->create(['name' => 'Deleted Brand']);
        $deletedBrand->delete();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard', [
                'locale' => 'en',
                'tab' => 'brands',
                'show_deleted' => '1'
            ]));

        $response->assertStatus(200);
        $response->assertSee('Active Brand');
        $response->assertSee('Deleted Brand');
    }

    public function test_restore_recovers_soft_deleted_brand()
    {
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $brand->delete();

        $this->assertSoftDeleted('brands', ['id' => $brand->id]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.brands.restore', ['locale' => 'en', 'id' => $brand->id]));

        $response->assertRedirect(route('admin.dashboard', ['locale' => 'en', 'tab' => 'brands']));
        $response->assertSessionHas('success', __('brands.messages.restored'));

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'deleted_at' => null
        ]);
    }

    public function test_force_delete_permanently_removes_brand()
    {
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $brand->delete(); // Soft delete first

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.brands.force-delete', ['locale' => 'en', 'id' => $brand->id]));

        $response->assertRedirect(route('admin.dashboard', ['locale' => 'en', 'tab' => 'brands']));
        $response->assertSessionHas('success', __('brands.messages.force_deleted'));

        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }
}

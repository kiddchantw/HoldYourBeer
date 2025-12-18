<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CreateBeer;
use App\Models\Beer;
use App\Models\Brand;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateBeerTwoStepTest extends TestCase
{
    use RefreshDatabase;

    public function test_step1_validation_prevents_moving_to_step2_if_brand_missing(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBeer::class)
            ->set('brand_name', '')
            ->set('name', 'Super Dry')
            ->call('nextStep')
            ->assertHasErrors(['brand_name'])
            ->assertSet('currentStep', 1); // Should stay on step 1
    }

    public function test_can_navigate_between_steps(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBeer::class)
            // Fill Step 1
            ->set('brand_name', 'Asahi')
            ->set('name', 'Super Dry')
            // Move to Step 2
            ->call('nextStep')
            ->assertSet('currentStep', 2)
            ->assertSee('Step 2 of 2')
            // Go back to Step 1
            ->call('previousStep')
            ->assertSet('currentStep', 1)
            ->assertSee('Step 1 of 2');
    }

    public function test_can_save_beer_with_shop_and_note(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBeer::class)
            // Step 1
            ->set('brand_name', 'Heineken')
            ->set('name', 'Silver')
            ->call('nextStep')
            // Step 2
            ->set('shop_name', '7-Eleven')
            ->set('note', 'Crisp and refreshing')
            ->call('save');

        // Verify Database
        $this->assertDatabaseHas('brands', ['name' => 'Heineken']);
        $this->assertDatabaseHas('beers', ['name' => 'Silver']);
        $this->assertDatabaseHas('shops', ['name' => '7-Eleven']);
        
        // Verify Tasting Log
        $this->assertDatabaseHas('tasting_logs', [
            'note' => 'Crisp and refreshing',
            // Check shop relationship via ID lookup
            'shop_id' => Shop::where('name', '7-Eleven')->first()->id,
        ]);
    }

    public function test_skip_and_save_works_identically_to_save_without_optional_fields(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBeer::class)
            // Step 1
            ->set('brand_name', 'Kirin')
            ->set('name', 'Ichiban')
            ->call('nextStep')
            // Step 2 (Empty)
            ->call('save'); // Direct save without optional fields

        // Verify Database
        $this->assertDatabaseHas('brands', ['name' => 'Kirin']);
        $this->assertDatabaseHas('beers', ['name' => 'Ichiban']);
        $this->assertDatabaseHas('tasting_logs', [
            'shop_id' => null, // No shop
            'note' => '',      // Empty note (default livewire value)
        ]);
    }

    public function test_shop_autocomplete_suggestions(): void
    {
        $user = User::factory()->create();
        
        // Create existing shops
        Shop::create(['name' => 'Costco']);
        Shop::create(['name' => 'Carrefour']);
        Shop::create(['name' => 'Circle K']);

        Livewire::actingAs($user)
            ->test(CreateBeer::class)
            ->set('shop_name', 'C')     // "C" (1 char) - should trigger search
            ->assertCount('shop_suggestions', 3) // Costco, Carrefour, Circle K
            ->set('shop_name', 'Ca')    // "Ca"
            ->assertCount('shop_suggestions', 1) 
            ->assertSeeInOrder(['Carrefour']);
    }

    public function test_can_save_beer_with_multiple_quantity(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBeer::class)
            // Step 1
            ->set('brand_name', 'Suntory')
            ->set('name', 'Premium Malt')
            ->call('nextStep')
            // Step 2
            ->call('incrementQuantity')
            ->call('incrementQuantity') // Qty = 3 (1 default + 2 increments)
            ->assertSet('quantity', 3)
            ->call('decrementQuantity') // Qty = 2
            ->assertSet('quantity', 2)
            ->call('save');

        // Verify Beer created
        $beer = Beer::where('name', 'Premium Malt')->first();
        $this->assertNotNull($beer);
        
        // Verify Count is 2
        $this->assertDatabaseHas('user_beer_counts', [
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 2,
        ]);
    }
}

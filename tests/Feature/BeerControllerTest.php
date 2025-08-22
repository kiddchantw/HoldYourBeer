<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeerControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an authenticated user can see the create beer page.
     */
    public function test_create_page_is_rendered_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('beers.create', ['locale' => 'en']));

        $response->assertStatus(200);
        $response->assertViewIs('beers.create');
    }

    /**
     * Test that a guest is redirected to the login page.
     */
    public function test_guest_is_redirected_from_create_page(): void
    {
        $response = $this->get(route('beers.create', ['locale' => 'en']));

        $response->assertRedirect(route('localized.login', ['locale' => 'en']));
    }

    /**
     * Test it can store a new beer and brand.
     */
    public function test_it_can_store_a_new_beer_and_brand(): void
    {
        $user = User::factory()->create();

        $beerData = [
            'brand_name' => 'Test Brand',
            'name' => 'Test Beer',
            'style' => 'IPA',
        ];

        $response = $this->actingAs($user)->post(route('beers.store', ['locale' => 'en']), $beerData);

        $response->assertRedirect(route('localized.dashboard', ['locale' => 'en']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('brands', [
            'name' => 'Test Brand',
        ]);

        $this->assertDatabaseHas('beers', [
            'name' => 'Test Beer',
            'style' => 'IPA',
        ]);

        $this->assertDatabaseHas('user_beer_counts', [
            'user_id' => $user->id,
            'count' => 1,
        ]);
    }
}

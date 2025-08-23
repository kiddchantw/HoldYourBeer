<?php

namespace Tests\Feature;

use App\Models\Beer;
use App\Models\Brand;
use App\Models\User;
use App\Models\UserBeerCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_the_dashboard_with_a_list_of_beers()
    {
        $user = User::factory()->create();
        $brand1 = Brand::factory()->create(['name' => 'Guinness']);
        $beer1 = Beer::factory()->create(['brand_id' => $brand1->id, 'name' => 'Draught']);
        UserBeerCount::factory()->create(['user_id' => $user->id, 'beer_id' => $beer1->id, 'count' => 5, 'last_tasted_at' => '2025-08-10 20:00:00']);

        $brand2 = Brand::factory()->create(['name' => 'Brewdog']);
        $beer2 = Beer::factory()->create(['brand_id' => $brand2->id, 'name' => 'Punk IPA']);
        UserBeerCount::factory()->create(['user_id' => $user->id, 'beer_id' => $beer2->id, 'count' => 3, 'last_tasted_at' => '2025-08-12 21:00:00']);

        $brand3 = Brand::factory()->create(['name' => 'Asahi']);
        $beer3 = Beer::factory()->create(['brand_id' => $brand3->id, 'name' => 'Super Dry']);
        UserBeerCount::factory()->create(['user_id' => $user->id, 'beer_id' => $beer3->id, 'count' => 8, 'last_tasted_at' => '2025-08-11 18:00:00']);

        $response = $this->actingAs($user)->get('/en/dashboard');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Punk IPA', 'Super Dry', 'Draught']);
        $response->assertSee('Punk IPA');
        $response->assertSee('Super Dry');
        $response->assertSee('Draught');
    }

    public function test_it_displays_the_empty_state_when_there_are_no_beers()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/en/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Track my first beer');
    }
}

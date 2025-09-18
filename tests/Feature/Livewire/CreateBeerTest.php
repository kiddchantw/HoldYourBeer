<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CreateBeer;
use App\Models\User;
use App\Models\Brand;
use App\Models\Beer;
use App\Models\UserBeerCount;
use App\Models\TastingLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Related specifications: spec/features/beer_tracking/adding_a_beer.feature
 * Related specifications: spec/features/beer_tracking/managing_tastings.feature
 * Related specifications: spec/features/loading_states.feature
 *
 * Scenarios covered:
 * - Adding new beers with brand creation
 * - Adding existing beers to user collection
 * - Adding beers with tasting notes
 * - Incrementing count for already tracked beers
 * - Form validation for required fields
 *
 * Test coverage:
 * - Livewire component rendering
 * - Beer creation with brand handling
 * - Tasting note functionality
 * - Existing beer tracking
 * - Count increment for re-tracked beers
 * - Form validation and error handling
 */
class CreateBeerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_component_can_render()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateBeer::class)
            ->assertStatus(200);
    }

    #[Test]
    public function brand_name_is_required()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateBeer::class)
            ->set('name', 'Test Beer')
            ->call('save')
            ->assertHasErrors(['brand_name' => 'required']);
    }

    #[Test]
    public function name_is_required()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateBeer::class)
            ->set('brand_name', 'Test Brand')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    #[Test]
    public function it_can_save_a_new_beer_and_redirects()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateBeer::class)
            ->set('brand_name', 'New Awesome Brand')
            ->set('name', 'Awesome Beer')
            ->set('style', 'Lager')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('localized.dashboard', ['locale' => 'en']));

        $this->assertDatabaseHas('brands', [
            'name' => 'New Awesome Brand'
        ]);
        $this->assertDatabaseHas('beers', [
            'name' => 'Awesome Beer',
            'style' => 'Lager'
        ]);
        $this->assertDatabaseHas('user_beer_counts', [
            'user_id' => $user->id,
            'count' => 1
        ]);
    }

    #[Test]
    public function it_can_save_a_new_beer_with_a_tasting_note()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateBeer::class)
            ->set('brand_name', 'New Awesome Brand')
            ->set('name', 'Awesome Beer')
            ->set('style', 'Lager')
            ->set('note', 'This is a test note.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('localized.dashboard', ['locale' => 'en']));

        $this->assertDatabaseHas('tasting_logs', [
            'note' => 'This is a test note.',
            'action' => 'initial'
        ]);
    }

    #[Test]
    public function it_can_track_an_existing_beer_not_yet_tracked_by_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $brand = Brand::factory()->create(['name' => 'Existing Brand']);
        $beer = Beer::factory()->create(['brand_id' => $brand->id, 'name' => 'Existing Beer']);

        Livewire::test(CreateBeer::class)
            ->set('brand_name', 'Existing Brand')
            ->set('name', 'Existing Beer')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('localized.dashboard', ['locale' => 'en']));

        $this->assertDatabaseHas('user_beer_counts', [
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 1,
        ]);
        $this->assertDatabaseHas('tasting_logs', [
            'user_beer_count_id' => UserBeerCount::where('user_id', $user->id)->where('beer_id', $beer->id)->first()->id,
            'action' => 'initial',
            'note' => '',
        ]);
    }

    #[Test]
    public function it_increments_count_when_adding_an_already_tracked_beer()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $brand = Brand::factory()->create(['name' => 'Existing Brand']);
        $beer = Beer::factory()->create(['brand_id' => $brand->id, 'name' => 'Existing Beer']);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 5,
        ]);

        Livewire::test(CreateBeer::class)
            ->set('brand_name', 'Existing Brand')
            ->set('name', 'Existing Beer')
            ->set('note', 'Another tasting.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('localized.dashboard', ['locale' => 'en']));

        $this->assertDatabaseHas('user_beer_counts', [
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 6,
        ]);
        $this->assertDatabaseHas('tasting_logs', [
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'increment',
            'note' => 'Another tasting.',
        ]);
    }
}

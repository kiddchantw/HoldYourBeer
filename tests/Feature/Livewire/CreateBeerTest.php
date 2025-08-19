<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CreateBeer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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
            ->assertRedirect(route('dashboard'));

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
}

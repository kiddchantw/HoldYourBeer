<?php

namespace Tests\Feature\Livewire;

use App\Livewire\TastingHistory;
use App\Models\Beer;
use App\Models\Brand;
use App\Models\TastingLog;
use App\Models\User;
use App\Models\UserBeerCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests for the TastingHistory Livewire component.
 * 
 * These tests verify:
 * - Date grouping logic for tasting logs
 * - Daily total calculation
 * - Correct display of grouped entries
 * - Empty state handling
 */
class TastingHistoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_component_can_render()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->assertStatus(200);
    }

    #[Test]
    public function it_groups_tasting_logs_by_date()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        // Create logs on the same day (Asia/Taipei timezone)
        // 2025-12-26 00:00 Asia/Taipei = 2025-12-25 16:00 UTC
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'initial',
            'tasted_at' => '2025-12-25 16:00:00', // Dec 26 in Asia/Taipei
        ]);
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'increment',
            'tasted_at' => '2025-12-25 18:00:00', // Dec 26 in Asia/Taipei
        ]);

        // Create a log on a different day
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'increment',
            'tasted_at' => '2025-12-22 16:00:00', // Dec 23 in Asia/Taipei
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TastingHistory::class, ['beerId' => $beer->id]);

        // Get the grouped logs from the component
        $groupedLogs = $component->viewData('groupedLogs');

        // Should have 2 date groups
        $this->assertCount(2, $groupedLogs);

        // Check the keys are dates
        $this->assertTrue($groupedLogs->has('2025-12-26'));
        $this->assertTrue($groupedLogs->has('2025-12-23'));
    }

    #[Test]
    public function it_calculates_daily_total_correctly()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        // Create 3 logs on the same day (1 initial + 2 increments = 3 total)
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'initial',
            'tasted_at' => '2025-12-25 16:00:00',
        ]);
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'increment',
            'tasted_at' => '2025-12-25 17:00:00',
        ]);
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'increment',
            'tasted_at' => '2025-12-25 18:00:00',
        ]);

        // Create a decrement (should be subtracted from total)
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'decrement',
            'tasted_at' => '2025-12-25 19:00:00',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TastingHistory::class, ['beerId' => $beer->id]);
        $groupedLogs = $component->viewData('groupedLogs');

        // Dec 26 in Asia/Taipei should have 2 total (1 initial + 2 increments - 1 decrement = 2)
        $this->assertEquals(2, $groupedLogs['2025-12-26']['total_daily']);
    }

    #[Test]
    public function it_displays_tasting_notes_for_each_log()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'initial',
            'note' => '果香濃郁',
            'tasted_at' => '2025-12-25 16:00:00',
        ]);
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'increment',
            'note' => '尾韻微苦',
            'tasted_at' => '2025-12-25 18:00:00',
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->assertSee('果香濃郁')
            ->assertSee('尾韻微苦');
    }

    #[Test]
    public function it_shows_empty_state_when_no_logs_exist()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->assertSee(__('No tasting records yet'));
    }

    #[Test]
    public function it_orders_logs_by_date_descending()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        // Create logs on different days
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'initial',
            'tasted_at' => '2025-12-20 16:00:00', // Dec 21 in Asia/Taipei (older)
        ]);
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'increment',
            'tasted_at' => '2025-12-25 16:00:00', // Dec 26 in Asia/Taipei (newer)
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TastingHistory::class, ['beerId' => $beer->id]);
        $groupedLogs = $component->viewData('groupedLogs');

        // The first key should be the newest date
        $keys = $groupedLogs->keys()->toArray();
        $this->assertEquals('2025-12-26', $keys[0]);
        $this->assertEquals('2025-12-21', $keys[1]);
    }

    #[Test]
    public function it_displays_formatted_date_in_view()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'initial',
            'tasted_at' => '2025-12-25 16:00:00', // Dec 26 in Asia/Taipei
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->assertSee('Dec 26, 2025');
    }

    #[Test]
    public function it_displays_daily_unit_count()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        // Create 2 increment logs on the same day
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'increment',
            'tasted_at' => '2025-12-25 16:00:00',
        ]);
        TastingLog::factory()->create([
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'increment',
            'tasted_at' => '2025-12-25 18:00:00',
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->assertSee('2 ' . __('units'));
    }

    // =============================================
    // Phase 2 Tests: Add Record Modal
    // =============================================

    #[Test]
    public function it_can_open_and_close_add_modal()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->assertSet('showAddModal', false)
            ->call('openAddModal')
            ->assertSet('showAddModal', true)
            ->call('closeAddModal')
            ->assertSet('showAddModal', false);
    }

    #[Test]
    public function it_can_increment_and_decrement_quantity()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->assertSet('quantity', 1)
            ->call('increaseQuantity')
            ->assertSet('quantity', 2)
            ->call('increaseQuantity')
            ->assertSet('quantity', 3)
            ->call('decreaseQuantity')
            ->assertSet('quantity', 2);
    }

    #[Test]
    public function it_cannot_decrement_quantity_below_1()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->assertSet('quantity', 1)
            ->call('decreaseQuantity')
            ->assertSet('quantity', 1); // Should stay at 1
    }

    #[Test]
    public function it_can_save_a_new_record()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 5,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->call('openAddModal')
            ->set('quantity', 2)
            ->set('note', '今天的味道特別好')
            ->call('saveRecord')
            ->assertSet('showAddModal', false)
            ->assertSet('successMessage', __('Record added successfully!'));

        // Verify database
        $this->assertDatabaseHas('user_beer_counts', [
            'id' => $userBeerCount->id,
            'count' => 7, // 5 + 2
        ]);

        // Verify tasting logs were created (2 logs for quantity 2)
        $this->assertEquals(2, TastingLog::where('user_beer_count_id', $userBeerCount->id)->count());
        
        // Verify note was saved on first log
        $this->assertDatabaseHas('tasting_logs', [
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'add',
            'note' => '今天的味道特別好',
        ]);
    }

    #[Test]
    public function it_saves_record_with_single_quantity()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 3,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->call('openAddModal')
            ->set('quantity', 1)
            ->call('saveRecord')
            ->assertSet('showAddModal', false);

        // Verify database
        $this->assertDatabaseHas('user_beer_counts', [
            'id' => $userBeerCount->id,
            'count' => 4, // 3 + 1
        ]);
    }

    #[Test]
    public function it_resets_form_when_modal_is_closed()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->call('openAddModal')
            ->set('quantity', 5)
            ->set('note', 'Some note')
            ->call('closeAddModal')
            ->assertSet('quantity', 1) // Reset to default
            ->assertSet('note', ''); // Reset to empty
    }

    #[Test]
    public function it_displays_add_record_button()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->assertSee(__('Add Record'));
    }

    #[Test]
    public function it_can_clear_success_message()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->call('openAddModal')
            ->call('saveRecord')
            ->assertSet('successMessage', __('Record added successfully!'))
            ->call('clearSuccessMessage')
            ->assertSet('successMessage', '');
    }

    // =============================================
    // Phase 3 Tests: Date Card Actions
    // =============================================

    #[Test]
    public function it_can_increment_count_for_a_date()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 5,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->call('addForDate', '2025-12-26')
            ->assertSet('successMessage', __('Added 1 unit!'));

        // Verify database
        $this->assertDatabaseHas('user_beer_counts', [
            'id' => $userBeerCount->id,
            'count' => 6, // 5 + 1
        ]);

        // Verify tasting log was created
        $this->assertDatabaseHas('tasting_logs', [
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'add',
        ]);
    }

    #[Test]
    public function it_can_decrement_count_for_a_date()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 5,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->call('deleteForDate', '2025-12-26')
            ->assertSet('successMessage', __('Removed 1 unit!'));

        // Verify database
        $this->assertDatabaseHas('user_beer_counts', [
            'id' => $userBeerCount->id,
            'count' => 4, // 5 - 1
        ]);

        // Verify tasting log was created
        $this->assertDatabaseHas('tasting_logs', [
            'user_beer_count_id' => $userBeerCount->id,
            'action' => 'delete',
        ]);
    }

    #[Test]
    public function it_cannot_decrement_count_below_zero()
    {
        $user = User::factory()->create();
        $brand = Brand::factory()->create();
        $beer = Beer::factory()->create(['brand_id' => $brand->id]);
        $userBeerCount = UserBeerCount::factory()->create([
            'user_id' => $user->id,
            'beer_id' => $beer->id,
            'count' => 0,
        ]);

        $this->actingAs($user);

        Livewire::test(TastingHistory::class, ['beerId' => $beer->id])
            ->call('deleteForDate', '2025-12-26');

        // Verify count stayed at 0
        $this->assertDatabaseHas('user_beer_counts', [
            'id' => $userBeerCount->id,
            'count' => 0,
        ]);

        // No decrement log should be created
        $this->assertEquals(0, TastingLog::where('user_beer_count_id', $userBeerCount->id)->count());
    }
}

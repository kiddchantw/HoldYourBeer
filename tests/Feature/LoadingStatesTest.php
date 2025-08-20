<?php

namespace Tests\Feature;

use App\Livewire\CreateBeer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LoadingStatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    #[Test]
    public function loading_spinner_and_message_appear_when_saving_beer()
    {
        $component = Livewire::test(CreateBeer::class);

        // Assert that the loading overlay div exists and contains the message
        $component->assertSeeHtml('wire:loading.flex');
        $component->assertSeeHtml('wire:target="save"');
        $component->assertSeeHtml('Saving beer...');

        // Assert that the button has the disabled attribute when loading
        $component->assertSeeHtml('wire:loading.attr="disabled"');
        $component->assertSeeHtml('wire:target="save"');

        // Assert that the "Saving..." text appears within the button\'s loading span
        $component->assertSeeHtml('<span wire:loading wire:target="save">Saving...</span>');

        // Assert that input fields have wire:loading.attr="disabled"
        // Assert that input fields have wire:loading.attr="disabled" and wire:target="save"
        $component->assertSeeHtml('wire:loading.attr="disabled"');
        $component->assertSeeHtml('wire:target="save"');
    }
}

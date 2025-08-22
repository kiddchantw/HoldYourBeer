<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MultilingualSwitchingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_switches_language_and_displays_correct_content()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/zh-TW/dashboard')
            ->assertSee('儀表板');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertSee('Dashboard');
    }

    #[Test]
    public function it_redirects_to_the_correct_locale_url_if_not_set()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect('/dashboard');
    }

    #[Test]
    public function it_displays_language_switcher_with_correct_options()
    {
        $user = User::factory()->create();

        // Test English dashboard has language switcher
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertSee('EN'); // Current language indicator
        $response->assertSee('繁體中文'); // Option to switch to Chinese

        // Test Chinese dashboard has language switcher
        $response = $this->actingAs($user)->get('/zh-TW/dashboard');
        $response->assertSee('ZH-TW'); // Current language indicator
        $response->assertSee('English'); // Option to switch to English
    }
}

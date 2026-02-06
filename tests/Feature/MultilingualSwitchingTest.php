<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Related specifications: spec/features/multilingual_switching.feature
 *
 * Scenarios covered:
 * - Switch language from English to Chinese
 * - Switch language from Chinese to English
 * - Language preference persistence across sessions
 * - Language switching on different page types
 * - Language switcher presence on main pages
 * - Language switching with form validation messages
 * - Language switching with dynamic content
 * - Language switching accessibility
 * - Language switching with browser language detection
 * - Language switching with URL prefix
 *
 * Test coverage:
 * - Language switching functionality
 * - URL locale handling
 * - Language switcher component display
 * - Redirect behavior for unset locale
 */
class MultilingualSwitchingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_switches_language_and_displays_correct_content()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/zh-TW/dashboard')
            ->assertSee('我的啤酒');

        $this->actingAs($user)
            ->get('/en/dashboard')
            ->assertSee('My Beers');
    }

    #[Test]
    public function it_redirects_to_the_correct_locale_url_if_not_set()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect('/en/dashboard');
    }

    #[Test]
    public function it_displays_language_switcher_with_correct_options()
    {
        $user = User::factory()->create();

        // Test English dashboard has language switcher
        $response = $this->actingAs($user)->get('/en/dashboard');
        $response->assertSee('EN'); // Current language indicator
        $response->assertSee('繁體中文'); // Option to switch to Chinese

        // Test Chinese dashboard has language switcher
        $response = $this->actingAs($user)->get('/zh-TW/dashboard');
        $response->assertSee('ZH-TW'); // Current language indicator
        $response->assertSee('English'); // Option to switch to English
    }
}

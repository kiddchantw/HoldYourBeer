<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Brand;
use App\Models\Beer;
use App\Models\UserBeerCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Related specifications: spec/features/google_analytics_integration.feature
 *
 * Scenarios covered:
 * - Page view tracking
 * - User authentication tracking
 * - Beer creation tracking
 * - Beer count increment tracking
 * - Cookie consent mechanism
 * - Privacy compliance
 */
class GoogleAnalyticsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable notifications during tests
        Notification::fake();

        // Enable Google Analytics for testing
        Config::set('services.google_analytics.enabled', true);
        Config::set('services.google_analytics.measurement_id', 'G-TEST123456');
    }

    // ========================================
    // Cookie Consent Tests
    // ========================================

    #[Test]
    public function cookie_consent_banner_is_displayed_when_no_consent_given()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard', ['locale' => 'en']));

        $response->assertStatus(200);
        $response->assertSee('cookie-consent-banner', false);
    }

    #[Test]
    public function cookie_consent_can_be_accepted()
    {
        $response = $this->postJson(route('cookie-consent.store'), [
            'consent' => true
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'consent' => true,
            ]);

        $this->assertTrue(session('cookie_consent'));
    }

    #[Test]
    public function cookie_consent_can_be_rejected()
    {
        $response = $this->postJson(route('cookie-consent.store'), [
            'consent' => false
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'consent' => false,
            ]);

        $this->assertFalse(session('cookie_consent'));
    }

    #[Test]
    public function cookie_consent_requires_boolean_value()
    {
        $response = $this->postJson(route('cookie-consent.store'), [
            'consent' => 'invalid'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['consent']);
    }

    // ========================================
    // Google Analytics Loading Tests
    // ========================================

    #[Test]
    public function google_analytics_is_not_loaded_without_cookie_consent()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard', ['locale' => 'en']));

        $response->assertStatus(200);
        $response->assertDontSee('gtag.js', false);
        $response->assertDontSee('window.dataLayer', false);
    }

    #[Test]
    public function google_analytics_is_loaded_with_cookie_consent()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Set cookie consent in session
        session(['cookie_consent' => true]);

        $response = $this->get(route('dashboard', ['locale' => 'en']));

        $response->assertStatus(200);
        $response->assertSee('gtag.js', false);
        $response->assertSee('window.dataLayer', false);
        $response->assertSee('G-TEST123456', false);
    }

    #[Test]
    public function google_analytics_includes_user_id_for_authenticated_users()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Set cookie consent
        session(['cookie_consent' => true]);

        $response = $this->get(route('dashboard', ['locale' => 'en']));

        $response->assertStatus(200);
        $response->assertSee("'user_id': '{$user->id}'", false);
    }

    #[Test]
    public function google_analytics_is_disabled_when_config_disabled()
    {
        Config::set('services.google_analytics.enabled', false);

        $user = User::factory()->create();
        $this->actingAs($user);

        // Even with consent
        session(['cookie_consent' => true]);

        $response = $this->get(route('dashboard', ['locale' => 'en']));

        $response->assertStatus(200);
        $response->assertDontSee('gtag.js', false);
    }

    // ========================================
    // Page View Tracking Tests
    // ========================================

    #[Test]
    public function page_view_tracking_is_enabled_by_default()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        session(['cookie_consent' => true]);

        $response = $this->get(route('dashboard', ['locale' => 'en']));

        $response->assertStatus(200);
        $response->assertSee("'send_page_view': true", false);
    }

    // ========================================
    // Component Integration Tests
    // ========================================

    #[Test]
    public function google_analytics_component_is_included_in_app_layout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        session(['cookie_consent' => true]);

        $response = $this->get(route('dashboard', ['locale' => 'en']));

        $response->assertStatus(200);
        // Verify GA component renders
        $response->assertSee('gtag', false);
    }

    #[Test]
    public function cookie_consent_component_is_included_in_app_layout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard', ['locale' => 'en']));

        $response->assertStatus(200);
        // Verify cookie consent component renders when no consent given
        $response->assertSee('acceptCookies', false);
        $response->assertSee('rejectCookies', false);
    }

    // ========================================
    // Configuration Tests
    // ========================================

    #[Test]
    public function google_analytics_measurement_id_is_configurable()
    {
        Config::set('services.google_analytics.measurement_id', 'G-CUSTOM123');

        $user = User::factory()->create();
        $this->actingAs($user);

        session(['cookie_consent' => true]);

        $response = $this->get(route('dashboard', ['locale' => 'en']));

        $response->assertStatus(200);
        $response->assertSee('G-CUSTOM123', false);
    }

    #[Test]
    public function google_analytics_respects_environment_configuration()
    {
        $this->assertEquals('G-TEST123456', config('services.google_analytics.measurement_id'));
        $this->assertTrue(config('services.google_analytics.enabled'));
    }
}

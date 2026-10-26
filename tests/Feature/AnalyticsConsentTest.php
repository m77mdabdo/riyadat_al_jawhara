<?php

namespace Tests\Feature;

use App\Support\Analytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_everything_is_off_when_no_provider_is_configured(): void
    {
        config(['analytics.ga4_id' => null, 'analytics.plausible_domain' => null]);

        $response = $this->get(route('home'));

        $response->assertDontSee('data-analytics', false)
            ->assertDontSee(__('site.consent.title'))
            ->assertDontSee(__('site.consent.settings'));
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringNotContainsString('googletagmanager', $csp);
        $this->assertStringNotContainsString('plausible', $csp);
    }

    public function test_ga4_shows_the_banner_and_opens_the_csp_only_for_google(): void
    {
        config(['analytics.ga4_id' => 'G-TEST1234', 'analytics.plausible_domain' => null]);

        $response = $this->get(route('home'));

        $response->assertSee(__('site.consent.title'))
            ->assertSee(__('site.consent.accept'))
            ->assertSee(__('site.consent.decline'))
            ->assertSee(__('site.consent.settings'))
            ->assertSee('data-analytics="{&quot;ga4&quot;:&quot;G-TEST1234&quot;}"', false);

        // Consent first: the page itself never includes the provider script.
        $response->assertDontSee('<script src="https://www.googletagmanager.com', false);

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertMatchesRegularExpression("#script-src [^;]*https://www\.googletagmanager\.com#", $csp);
        $this->assertMatchesRegularExpression('#connect-src [^;]*google-analytics\.com#', $csp);
        $this->assertStringNotContainsString('plausible', $csp);
    }

    public function test_plausible_is_supported_and_scoped_in_the_csp(): void
    {
        config(['analytics.ga4_id' => null, 'analytics.plausible_domain' => 'example.sa', 'analytics.plausible_src' => 'https://plausible.io/js/script.js']);

        $response = $this->get(route('home'));

        $response->assertSee('&quot;domain&quot;:&quot;example.sa&quot;', false);
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertMatchesRegularExpression('#script-src [^;]*https://plausible\.io#', $csp);
        $this->assertStringNotContainsString('googletagmanager', $csp);
    }

    public function test_malformed_ids_keep_analytics_off(): void
    {
        config(['analytics.ga4_id' => '"><script>', 'analytics.plausible_domain' => 'x', 'analytics.plausible_src' => 'http://insecure.example/js']);

        $this->assertFalse(Analytics::enabled());
        $this->get(route('home'))->assertDontSee('data-analytics', false);
    }

    public function test_consent_banner_labels_are_translated(): void
    {
        config(['analytics.ga4_id' => 'G-TEST1234']);

        $this->useLocale('ar');
        $this->get(route('home'))->assertSee('ملفات تعريف الارتباط والخصوصية')->assertSee('قبول التحليلات');
    }
}

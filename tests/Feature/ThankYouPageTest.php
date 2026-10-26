<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ThankYouPageTest extends TestCase
{
    use RefreshDatabase;

    private function submit(array $overrides = []): TestResponse
    {
        return $this->post(route('contact.store'), $overrides + ['name' => 'C', 'phone' => '0500000000', 'message' => 'Quote', 'consent' => '1']);
    }

    public function test_submission_lands_on_the_thank_you_page_in_the_same_language(): void
    {
        foreach (['ar', 'en'] as $locale) {
            $this->useLocale($locale);
            $this->submit()->assertRedirect(route('contact.thanks'));
            $this->assertStringStartsWith(url("/{$locale}/contact/thank-you"), route('contact.thanks'));
        }
    }

    public function test_thank_you_page_fires_the_conversion_only_after_a_real_submission(): void
    {
        $this->followingRedirects()->submit()
            ->assertOk()
            ->assertSee(__('site.contact.thanks_heading'))
            ->assertSee('<meta name="jra-conversion" content="lead_submitted">', false);

        // Visiting directly (or reloading) is not a conversion.
        $this->get(route('contact.thanks'))->assertOk()->assertDontSee('jra-conversion', false);
    }

    public function test_honeypot_gets_the_same_page_without_a_conversion(): void
    {
        $this->followingRedirects()->submit(['website' => 'spam'])
            ->assertOk()
            ->assertSee(__('site.contact.thanks_heading'))
            ->assertDontSee('jra-conversion', false);
    }

    public function test_thank_you_page_is_noindex_and_offers_whatsapp(): void
    {
        Setting::editable()->update(['whatsapp' => '966500000000']);

        $this->get(route('contact.thanks'))
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('https://wa.me/966500000000', false)
            ->assertSee(__('site.contact.thanks_steps')[0]);
    }
}

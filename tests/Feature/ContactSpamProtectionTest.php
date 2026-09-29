<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactSpamProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function validLead(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Real Customer',
            'phone' => '0500000000',
            'message' => 'I need a quote for 80 m² of marble flooring.',
        ], $overrides);
    }

    public function test_normal_submission_creates_a_lead(): void
    {
        $this->from(route('contact'))
            ->post(route('contact.store'), $this->validLead())
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success', __('site.contact.success_message'));

        $this->assertDatabaseHas('leads', ['name' => 'Real Customer', 'status' => 'new', 'source' => 'website']);
    }

    public function test_filled_honeypot_stores_nothing_but_looks_successful(): void
    {
        $this->from(route('contact'))
            ->post(route('contact.store'), $this->validLead(['website' => 'http://spam.example']))
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success', __('site.contact.success_message'));

        $this->assertSame(0, Lead::count());
    }

    public function test_contact_form_renders_the_honeypot_out_of_tab_order(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('name="website" id="website-contact" tabindex="-1" autocomplete="off"', false);
    }

    public function test_throttle_kicks_in_after_five_submissions_and_keeps_input(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->from(route('contact'))
                ->post(route('contact.store'), $this->validLead(['name' => "Lead {$i}"]))
                ->assertSessionHas('success');
        }

        $response = $this->from(route('contact'))
            ->post(route('contact.store'), $this->validLead(['name' => 'Lead 6']));

        $response->assertRedirect(route('contact'))
            ->assertSessionHas('error', __('site.contact.throttled'))
            ->assertSessionHasInput('name', 'Lead 6')
            ->assertSessionHasInput('message');

        $this->assertSame(5, Lead::count());
    }

    public function test_throttle_message_is_translated_in_arabic(): void
    {
        $this->get('/lang/ar');

        for ($i = 0; $i < 5; $i++) {
            $this->from(route('contact'))->post(route('contact.store'), $this->validLead());
        }

        $this->from(route('contact'))
            ->post(route('contact.store'), $this->validLead())
            ->assertSessionHas('error', trans('site.contact.throttled', [], 'ar'));

        $this->followingRedirects()
            ->from(route('contact'))
            ->post(route('contact.store'), $this->validLead())
            ->assertSee(trans('site.contact.throttled', [], 'ar'));
    }

    public function test_throttle_is_per_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
                ->post(route('contact.store'), $this->validLead());
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->from(route('contact'))
            ->post(route('contact.store'), $this->validLead(['name' => 'Other visitor']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leads', ['name' => 'Other visitor']);
    }
}

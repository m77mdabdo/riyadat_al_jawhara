<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StoneCategory;
use App\Models\StoneType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactContextTest extends TestCase
{
    use RefreshDatabase;

    private Service $service;

    private StoneType $stone;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = Service::create(['name_en' => 'Marble Installation', 'name_ar' => 'تركيب الرخام', 'is_active' => true]);
        $category = StoneCategory::create(['name_en' => 'Marble', 'name_ar' => 'رخام']);
        $this->stone = StoneType::create(['stone_category_id' => $category->id, 'name_en' => 'Carrara', 'name_ar' => 'كرارا', 'is_active' => true]);
        $this->project = Project::create([
            'title_en' => 'Villa Lobby', 'title_ar' => 'ردهة فيلا',
            'service_id' => $this->service->id, 'stone_type_id' => $this->stone->id, 'is_active' => true,
        ]);
    }

    private function valid(array $overrides = []): array
    {
        return array_merge(['name' => 'Customer', 'phone' => '0500000000', 'message' => 'Quote', 'consent' => '1'], $overrides);
    }

    public function test_service_and_stone_query_preselect_the_form(): void
    {
        $this->get(route('contact', ['service' => $this->service->slug, 'stone_type' => $this->stone->slug]))
            ->assertOk()
            ->assertSee('<option value="'.$this->service->id.'" selected>', false)
            ->assertSee('<option value="'.$this->stone->id.'" selected>', false);
    }

    public function test_project_query_adds_project_and_its_service_and_stone(): void
    {
        $this->get(route('contact', ['project' => $this->project->slug]))
            ->assertOk()
            ->assertSee('name="project_id" value="'.$this->project->id.'"', false)
            ->assertSee('Villa Lobby')
            ->assertSee('<option value="'.$this->service->id.'" selected>', false)
            ->assertSee('<option value="'.$this->stone->id.'" selected>', false);
    }

    public function test_unknown_or_inactive_context_is_ignored(): void
    {
        $this->project->update(['is_active' => false]);

        $this->get(route('contact', ['project' => $this->project->slug, 'service' => 'nope']))
            ->assertOk()
            ->assertDontSee('name="project_id"', false)
            ->assertDontSee(' selected>', false);
    }

    public function test_cta_links_carry_context(): void
    {
        $this->get(route('services.show', $this->service))->assertSee(route('contact', ['service' => $this->service->slug]), false);
        $this->get(route('stones.show', $this->stone))->assertSee(route('contact', ['stone_type' => $this->stone->slug]), false);
        $this->get(route('projects.show', $this->project))->assertSee(route('contact', ['project' => $this->project->slug]), false);
    }

    public function test_lead_stores_context_and_metadata(): void
    {
        $this->withHeader('User-Agent', 'TestBrowser/1.0')
            ->withHeader('referer', route('contact', ['project' => $this->project->slug]))
            ->post(route('contact.store'), $this->valid([
                'service_id' => $this->service->id,
                'stone_type_id' => $this->stone->id,
                'project_id' => $this->project->id,
            ]))
            ->assertSessionHasNoErrors();

        $lead = Lead::firstOrFail();
        $this->assertSame($this->project->id, $lead->project_id);
        $this->assertSame('en', $lead->locale);
        $this->assertStringContainsString('project='.$this->project->slug, $lead->source_url);
        $this->assertSame('127.0.0.1', $lead->ip_address);
        $this->assertSame('TestBrowser/1.0', $lead->user_agent);
        $this->assertNotNull($lead->consented_at);
    }

    public function test_locale_is_recorded(): void
    {
        $this->get('/lang/ar');
        $this->post(route('contact.store'), $this->valid());

        $this->assertSame('ar', Lead::firstOrFail()->locale);
    }

    public function test_consent_is_required(): void
    {
        $this->post(route('contact.store'), $this->valid(['consent' => null]))->assertSessionHasErrors('consent');
        $this->assertSame(0, Lead::count());
    }

    public function test_consent_checkbox_links_to_privacy_page(): void
    {
        $this->get(route('contact'))
            ->assertSee('name="consent"', false)
            ->assertSee('href="'.route('legal.privacy').'"', false);
    }

    public function test_inactive_service_stone_or_project_is_rejected(): void
    {
        $this->service->update(['is_active' => false]);
        $this->stone->update(['is_active' => false]);
        $this->project->update(['is_active' => false]);

        $this->post(route('contact.store'), $this->valid([
            'service_id' => $this->service->id,
            'stone_type_id' => $this->stone->id,
            'project_id' => $this->project->id,
        ]))->assertSessionHasErrors(['service_id', 'stone_type_id', 'project_id']);
    }

    public function test_arabic_digits_in_phone_are_normalized(): void
    {
        $this->post(route('contact.store'), $this->valid(['phone' => '٠٥٠١٢٣٤٥٦٧']))->assertSessionHasNoErrors();

        $this->assertSame('0501234567', Lead::firstOrFail()->phone);
    }

    public function test_garbage_phone_is_rejected(): void
    {
        $this->post(route('contact.store'), $this->valid(['phone' => 'call me maybe']))->assertSessionHasErrors('phone');
    }

    public function test_phone_input_has_mobile_keyboard_attributes(): void
    {
        $this->get(route('contact'))->assertSee('type="tel" name="phone" id="phone" inputmode="tel" autocomplete="tel"', false);
    }

    public function test_contact_links_are_clickable_and_whatsapp_is_normalized(): void
    {
        Setting::editable()->update(['phone' => '+966 55 123 4567', 'email' => 'info@company.test', 'whatsapp' => '+966 55-123 4567']);

        $html = $this->get(route('contact'))->assertOk()->getContent();

        $this->assertStringContainsString('href="tel:+966551234567"', $html);
        $this->assertStringContainsString('href="mailto:info@company.test"', $html);
        $this->assertStringContainsString('href="https://wa.me/966551234567"', $html);
        $this->assertStringContainsString('href="https://wa.me/966551234567?text=', $html);
        $this->assertStringNotContainsString('wa.me/+', $html);
    }
}

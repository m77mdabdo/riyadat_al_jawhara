<?php

namespace Tests\Feature;

use App\Filament\Widgets\LeadsOverview;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use App\Models\WhatsappClick;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WhatsappClickTest extends TestCase
{
    use RefreshDatabase;

    private function click(array $data = []): TestResponse
    {
        return $this->post(route('whatsapp.click'), $data + ['page' => '/ar/faq', 'locale' => 'ar']);
    }

    public function test_click_without_context_records_an_anonymous_event_only(): void
    {
        $this->click()->assertNoContent();

        $click = WhatsappClick::sole();
        $this->assertSame('/ar/faq', $click->page);
        $this->assertSame('ar', $click->locale);
        $this->assertNull($click->context_type);
        $this->assertNull($click->lead_id);
        $this->assertSame(0, Lead::count());
    }

    public function test_click_with_known_context_creates_one_whatsapp_lead_per_session(): void
    {
        $service = Service::factory()->create();
        $project = Project::factory()->create(['service_id' => $service->id]);

        $this->click(['page' => '/en/projects/'.$project->slug, 'locale' => 'en', 'context_type' => 'project', 'context_id' => $project->id])->assertNoContent();
        $this->click(['page' => '/en/projects/'.$project->slug, 'locale' => 'en', 'context_type' => 'project', 'context_id' => $project->id])->assertNoContent();

        $lead = Lead::sole();
        $this->assertSame('whatsapp', $lead->source);
        $this->assertSame($project->id, $lead->project_id);
        $this->assertSame($service->id, $lead->service_id);
        $this->assertSame('en', $lead->locale);
        $this->assertStringEndsWith('/en/projects/'.$project->slug, $lead->source_url);

        $this->assertSame(2, WhatsappClick::where('lead_id', $lead->id)->count());
    }

    public function test_unknown_or_inactive_context_is_treated_as_no_context(): void
    {
        $inactive = Service::factory()->inactive()->create();

        $this->click(['context_type' => 'service', 'context_id' => $inactive->id])->assertNoContent();
        $this->click(['context_type' => 'service', 'context_id' => 99999])->assertNoContent();

        $this->assertSame(0, Lead::count());
        $this->assertSame(2, WhatsappClick::whereNull('context_type')->count());
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->click(['context_type' => 'users', 'context_id' => 1])->assertSessionHasErrors('context_type');
        $this->click(['locale' => 'fr'])->assertSessionHasErrors('locale');
        $this->assertSame(0, WhatsappClick::count());
    }

    public function test_only_a_local_path_is_stored_and_nothing_personal(): void
    {
        $this->click(['page' => 'https://evil.example/x?y=1'])->assertNoContent();

        $this->assertSame('/x?y=1', WhatsappClick::sole()->page);
        foreach (['ip_address', 'ip', 'user_agent', 'email', 'phone'] as $column) {
            $this->assertFalse(Schema::hasColumn('whatsapp_clicks', $column), $column);
        }
    }

    public function test_beacon_is_rate_limited(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->click()->assertNoContent();
        }

        $this->click()->assertStatus(429);
    }

    public function test_whatsapp_links_are_tagged_and_detail_pages_carry_context(): void
    {
        Setting::editable()->update(['whatsapp' => '966500000000']);
        $project = Project::factory()->create();

        $html = $this->get(route('projects.show', $project))->getContent();

        $this->assertStringContainsString('data-wa-context-type="project" data-wa-context-id="'.$project->id.'"', $html);
        $this->assertStringContainsString('data-wa-endpoint="'.route('whatsapp.click').'"', $html);
        $this->assertStringContainsString('<meta name="csrf-token"', $html);
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'data-wa-track'));
    }

    public function test_dashboard_shows_whatsapp_clicks(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));
        WhatsappClick::factory()->count(3)->create();
        WhatsappClick::factory()->create(['created_at' => now()->subDays(40)]);

        Livewire::test(LeadsOverview::class)->assertSee('WhatsApp Clicks (30 days)')->assertSee('3');
    }
}

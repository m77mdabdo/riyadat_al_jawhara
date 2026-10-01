<?php

namespace Tests\Feature\Admin;

use App\Enums\LeadSource;
use App\Filament\Widgets\ContentHealthWidget;
use App\Filament\Widgets\FollowUpsDue;
use App\Filament\Widgets\LatestLeads;
use App\Filament\Widgets\LeadsByServiceChart;
use App\Filament\Widgets\LeadsChart;
use App\Filament\Widgets\LeadsOverview;
use App\Filament\Widgets\QuickActions;
use App\Filament\Widgets\TopStonesChart;
use App\Models\Lead;
use App\Models\Service;
use App\Models\StoneType;
use App\Models\User;
use App\Support\Dashboard;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
    }

    private function actingAsRole(string $role): void
    {
        $this->actingAs(tap(User::factory()->create())->assignRole($role));
    }

    public function test_dashboard_widgets_render_for_both_roles(): void
    {
        Lead::factory()->count(3)->create(['source' => LeadSource::Website->value]);
        Lead::factory()->create(['source' => LeadSource::Whatsapp->value, 'follow_up_at' => now()->subDay(), 'status' => 'contacted']);

        foreach (['admin', 'editor'] as $role) {
            $this->actingAsRole($role);
            $this->get('/admin')->assertOk()->assertDontSee('filament-info-widget');
            foreach ([LeadsOverview::class, LeadsChart::class, LeadsByServiceChart::class, TopStonesChart::class, LatestLeads::class, FollowUpsDue::class, ContentHealthWidget::class, QuickActions::class] as $widget) {
                Livewire::test($widget)->assertOk();
            }
        }

        $this->assertNotContains(FilamentInfoWidget::class, filament()->getPanel('admin')->getWidgets());
    }

    public function test_kpis_trends_and_charts(): void
    {
        $service = Service::factory()->create();
        $stone = StoneType::factory()->create();
        Lead::factory()->count(4)->create(['created_at' => now()->subDays(2), 'service_id' => $service->id, 'stone_type_id' => $stone->id]);
        Lead::factory()->count(2)->create(['created_at' => now()->subDays(10)]);
        $this->actingAsRole('admin');

        Livewire::test(LeadsOverview::class)
            ->assertSee('Leads this week')
            ->assertSee('+100%');

        $website = Dashboard::leadsPerDay(30, LeadSource::Website);
        $this->assertCount(30, $website);
        $this->assertSame([$service->id => 4, 'none' => 2], Dashboard::leadsByService());
        $this->assertSame([$stone->id => 4], Dashboard::topStones());
    }

    public function test_dashboard_figures_are_cached(): void
    {
        Lead::factory()->count(2)->create();
        Dashboard::kpis();

        DB::enableQueryLog();
        Dashboard::kpis();
        Dashboard::leadsPerDay(14);
        Dashboard::leadsPerDay(14);
        $this->assertCount(1, DB::getQueryLog(), 'only the first leadsPerDay(14) call queries');
    }

    public function test_status_can_be_changed_from_the_latest_leads_table(): void
    {
        $lead = Lead::factory()->create(['status' => 'new']);
        $this->actingAsRole('editor');

        Livewire::test(LatestLeads::class)
            ->assertCanSeeTableRecords([$lead])
            ->call('updateTableColumnState', 'status', (string) $lead->getKey(), 'contacted');

        $this->assertSame('contacted', $lead->fresh()->status);
    }

    public function test_content_health_lists_problems_with_links(): void
    {
        Service::factory()->create(['name_ar' => '']);
        $this->actingAsRole('admin');

        $problems = collect(Livewire::test(ContentHealthWidget::class)->instance()->problems())->keyBy('key');

        $this->assertSame(1, $problems['services_without_image']['count']);
        $this->assertStringContainsString('missing_image', urldecode($problems['services_without_image']['url']));
        $this->assertSame(1, $problems['services_missing_translation']['count']);
        $this->assertArrayHasKey('contact_missing', $problems->all());

        // The linked filter shows exactly that record.
        $this->get($problems['services_missing_translation']['url'])->assertOk();

        // Editors don't see the settings items (they can't open Settings).
        $this->actingAsRole('editor');
        $keys = collect(Livewire::test(ContentHealthWidget::class)->instance()->problems())->pluck('key');
        $this->assertNotContains('contact_missing', $keys);
    }
}

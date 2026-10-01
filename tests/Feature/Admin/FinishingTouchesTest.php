<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\FaqResource\Pages\ListFaqs;
use App\Filament\Resources\LeadResource\Pages\ListLeads;
use App\Filament\Resources\LeadResource\Pages\ViewLead;
use App\Filament\Resources\LeadResource\RelationManagers\NotesRelationManager;
use App\Filament\Resources\StoneTypeResource\Pages\EditStoneType;
use App\Filament\Resources\TeamMemberResource\Pages\ListTeamMembers;
use App\Filament\Resources\TestimonialResource\Pages\ListTestimonials;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\StoneAttribute;
use App\Models\StoneType;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinishingTouchesTest extends TestCase
{
    use RefreshDatabase;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $this->editor = tap(User::factory()->create())->assignRole('editor');
        $this->actingAs($this->editor);
    }

    public function test_a_missing_attribute_value_can_be_added_from_the_stone(): void
    {
        $stone = StoneType::factory()->create();

        $component = Livewire::test(EditStoneType::class, ['record' => $stone->getRouteKey()])
            ->callFormComponentAction('finishes', 'createOption', ['name_ar' => 'مطفي', 'name_en' => 'Matte']);
        $component->assertHasNoFormComponentActionErrors();

        $attribute = StoneAttribute::query()->where('name_en', 'Matte')->sole();
        $this->assertSame('finish', $attribute->type instanceof \BackedEnum ? $attribute->type->value : $attribute->type);
        $this->assertTrue((bool) $attribute->is_active);
    }

    public function test_record_titles_follow_the_admin_language(): void
    {
        $stone = StoneType::factory()->create(['name_ar' => 'رخام كرارا', 'name_en' => 'Carrara Marble']);

        $this->editor->update(['locale' => 'ar']);
        $this->get("/admin/stone-types/{$stone->id}/edit")->assertOk()->assertSee('تعديل رخام كرارا')->assertDontSee('تعديل Carrara Marble');

        $this->editor->update(['locale' => 'en']);
        $this->get("/admin/stone-types/{$stone->id}/edit")->assertOk()->assertSee('Edit Carrara Marble');
    }

    public function test_leads_have_status_tabs_and_grouping(): void
    {
        $new = Lead::factory()->create(['status' => 'new']);
        $closed = Lead::factory()->create(['status' => 'closed']);

        Livewire::test(ListLeads::class)
            ->assertCanSeeTableRecords([$new, $closed])
            ->set('activeTab', 'closed')
            ->assertCanSeeTableRecords([$closed])
            ->assertCanNotSeeTableRecords([$new])
            ->set('activeTab', 'all')
            ->set('tableGrouping', 'status')
            ->assertSee(__('enums.lead_status.closed'));
    }

    public function test_notes_show_as_a_timeline(): void
    {
        $lead = Lead::factory()->create();
        LeadNote::factory()->create(['lead_id' => $lead->id, 'user_id' => $this->editor->id, 'body' => "Called back.\nWill visit Sunday."]);

        Livewire::test(NotesRelationManager::class, ['ownerRecord' => $lead, 'pageClass' => ViewLead::class])
            ->assertSee('Will visit Sunday.')
            ->assertSee($this->editor->name);
    }

    public function test_testimonials_team_and_faq_have_a_bilingual_preview(): void
    {
        $testimonial = Testimonial::factory()->create(['comment_ar' => 'خدمة ممتازة', 'comment_en' => 'Excellent service', 'rating' => 5]);
        $member = TeamMember::factory()->create(['name_ar' => 'سارة', 'name_en' => 'Sara']);
        $faq = Faq::factory()->create(['question_ar' => 'كم المدة؟', 'question_en' => 'How long does it take?']);

        Livewire::test(ListTestimonials::class)->mountTableAction('preview', $testimonial)
            ->assertSee('خدمة ممتازة')->assertSee('Excellent service')->assertSee('★★★★★');
        Livewire::test(ListTeamMembers::class)->mountTableAction('preview', $member)
            ->assertSee('سارة')->assertSee('Sara');
        Livewire::test(ListFaqs::class)->mountTableAction('preview', $faq)
            ->assertSee('كم المدة؟')->assertSee('How long does it take?');
    }
}

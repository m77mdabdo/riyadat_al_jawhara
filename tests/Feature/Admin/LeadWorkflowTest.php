<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\LeadResource;
use App\Filament\Resources\LeadResource\Pages\EditLead;
use App\Filament\Resources\LeadResource\Pages\ListLeads;
use App\Filament\Resources\LeadResource\Pages\ViewLead;
use App\Filament\Resources\LeadResource\RelationManagers\NotesRelationManager;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\User;
use App\Support\LeadCsvExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeadWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $this->admin = tap(User::factory()->create(['name' => 'Admin A']))->assignRole('admin');
        $this->editor = tap(User::factory()->create(['name' => 'Editor E']))->assignRole('editor');
    }

    private function lead(array $attributes = []): Lead
    {
        return Lead::create($attributes + [
            'name' => 'Nasser', 'phone' => '+966 55 111 2222', 'email' => 'n@example.test',
            'message' => 'Need Carrara for 80 m2', 'status' => 'new', 'source' => 'website',
        ]);
    }

    public function test_staff_can_open_the_lead_view_page(): void
    {
        $lead = $this->lead();

        foreach ([$this->admin, $this->editor] as $user) {
            $this->actingAs($user)
                ->get(route('filament.admin.resources.leads.view', $lead))
                ->assertOk()
                ->assertSee('Nasser')
                ->assertSee('Need Carrara for 80 m2')
                ->assertSee('tel:+966551112222', false);
        }
    }

    public function test_lead_can_be_assigned_and_given_a_follow_up_date(): void
    {
        $this->actingAs($this->admin);
        $lead = $this->lead();

        Livewire::test(EditLead::class, ['record' => $lead->getRouteKey()])
            ->fillForm([
                'status' => 'contacted',
                'assigned_to' => $this->editor->id,
                'follow_up_at' => '2026-10-05',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $lead->refresh();
        $this->assertSame('contacted', $lead->status);
        $this->assertTrue($lead->assignee->is($this->editor));
        $this->assertSame('2026-10-05', $lead->follow_up_at->toDateString());
    }

    public function test_editor_can_add_a_note_attributed_to_them(): void
    {
        $this->actingAs($this->editor);
        $lead = $this->lead();

        Livewire::test(NotesRelationManager::class, ['ownerRecord' => $lead, 'pageClass' => ViewLead::class])
            ->callTableAction('create', data: ['body' => 'Called, sending quote tomorrow'])
            ->assertHasNoTableActionErrors();

        $note = LeadNote::firstOrFail();
        $this->assertSame('Called, sending quote tomorrow', $note->body);
        $this->assertTrue($note->user->is($this->editor));
        $this->assertTrue($note->lead->is($lead));
    }

    public function test_only_author_or_admin_can_edit_or_delete_a_note(): void
    {
        $lead = $this->lead();
        $adminNote = LeadNote::create(['lead_id' => $lead->id, 'user_id' => $this->admin->id, 'body' => 'admin note']);
        $editorNote = LeadNote::create(['lead_id' => $lead->id, 'user_id' => $this->editor->id, 'body' => 'editor note']);

        $this->actingAs($this->editor);
        Livewire::test(NotesRelationManager::class, ['ownerRecord' => $lead, 'pageClass' => ViewLead::class])
            ->assertTableActionHidden('delete', $adminNote)
            ->assertTableActionHidden('edit', $adminNote)
            ->assertTableActionVisible('delete', $editorNote);

        $this->actingAs($this->admin);
        Livewire::test(NotesRelationManager::class, ['ownerRecord' => $lead, 'pageClass' => ViewLead::class])
            ->assertTableActionVisible('delete', $editorNote)
            ->callTableAction('delete', $editorNote);

        $this->assertModelMissing($editorNote);
    }

    public function test_follow_up_due_scope_and_filter(): void
    {
        $this->actingAs($this->admin);
        $overdue = $this->lead(['name' => 'Overdue', 'follow_up_at' => today()->subDay()]);
        $today = $this->lead(['name' => 'Today', 'follow_up_at' => today()]);
        $future = $this->lead(['name' => 'Future', 'follow_up_at' => today()->addWeek()]);
        $closed = $this->lead(['name' => 'Closed', 'follow_up_at' => today()->subDay(), 'status' => 'closed']);

        $this->assertEqualsCanonicalizing([$overdue->id, $today->id], Lead::followUpDue()->pluck('id')->all());

        Livewire::test(ListLeads::class)
            ->filterTable('follow_up_due')
            ->assertCanSeeTableRecords([$overdue, $today])
            ->assertCanNotSeeTableRecords([$future, $closed]);
    }

    public function test_assigned_to_me_filter(): void
    {
        $this->actingAs($this->editor);
        $mine = $this->lead(['name' => 'Mine', 'assigned_to' => $this->editor->id]);
        $theirs = $this->lead(['name' => 'Theirs', 'assigned_to' => $this->admin->id]);

        Livewire::test(ListLeads::class)
            ->filterTable('mine')
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    public function test_admin_can_export_csv_and_editor_cannot(): void
    {
        $this->lead();

        $this->actingAs($this->admin);
        Livewire::test(ListLeads::class)
            ->assertActionVisible('exportCsv')
            ->callAction('exportCsv')
            ->assertFileDownloaded();

        $this->actingAs($this->editor);
        Livewire::test(ListLeads::class)->assertActionHidden('exportCsv');
    }

    public function test_csv_content_has_bom_headers_rows_and_formula_guard(): void
    {
        $this->lead(['name' => '=HYPERLINK("http://evil")', 'city' => 'الرياض']);
        $this->lead(['name' => 'Second', 'status' => 'closed']);

        ob_start();
        LeadCsvExporter::download(Lead::query())->sendContent();
        $csv = ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $lines = array_values(array_filter(explode("\n", substr($csv, 3))));
        $this->assertCount(3, $lines);
        $this->assertSame(LeadCsvExporter::HEADERS, str_getcsv($lines[0]));
        $this->assertStringContainsString("'=HYPERLINK", $lines[1]);
        $this->assertStringContainsString('الرياض', $lines[1]);
        $this->assertStringContainsString('Closed', $lines[2]);
    }

    public function test_safe_cell_prefixes_formula_triggers(): void
    {
        $this->assertSame("'=1+1", LeadCsvExporter::safeCell('=1+1'));
        $this->assertSame("'+966 55", LeadCsvExporter::safeCell('+966 55'));
        $this->assertSame("'@SUM(A1)", LeadCsvExporter::safeCell('@SUM(A1)'));
        $this->assertSame('0500000000', LeadCsvExporter::safeCell('0500000000'));
        $this->assertSame('', LeadCsvExporter::safeCell(null));
    }

    public function test_assignee_options_include_staff_only(): void
    {
        User::factory()->create(['name' => 'No Role']);

        $this->assertEqualsCanonicalizing(['Admin A', 'Editor E'], array_values(LeadResource::assigneeOptions()));
    }
}

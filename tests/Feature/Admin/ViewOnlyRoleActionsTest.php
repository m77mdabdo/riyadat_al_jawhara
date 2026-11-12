<?php

namespace Tests\Feature\Admin;

use App\Enums\CommentStatus;
use App\Enums\LeadStatus;
use App\Filament\Resources\CommentResource\Pages\ListComments;
use App\Filament\Resources\FaqResource\Pages\ListFaqs;
use App\Filament\Resources\LeadResource\Pages\ListLeads;
use App\Models\Comment;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * A role that may only view an area cannot change it through the shortcut
 * actions in the lists: comment moderation, the lead status dropdown and
 * "Duplicate".
 */
class ViewOnlyRoleActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('app:import-site-content');
    }

    private function viewer(): User
    {
        Role::create(['name' => 'Viewer', 'guard_name' => 'web'])
            ->givePermissionTo(['comments.view', 'leads.view', 'faqs.view']);

        return tap(User::factory()->create())->assignRole('Viewer');
    }

    public function test_a_viewer_cannot_approve_or_spam_a_comment(): void
    {
        $comment = Comment::factory()->create(['status' => CommentStatus::Pending->value]);
        $this->actingAs($this->viewer());

        $list = Livewire::test(ListComments::class);
        foreach (['approve', 'spam'] as $action) {
            $list->assertTableActionHidden($action, $comment)
                ->call('mountTableAction', $action, (string) $comment->getKey())
                ->call('callMountedTableAction');
        }

        $this->assertSame(CommentStatus::Pending->value, $comment->fresh()->status);

        // An editor (comments.update) still moderates.
        $this->actingAs(tap(User::factory()->create())->assignRole('editor'));
        Livewire::test(ListComments::class)->callTableAction('approve', $comment);
        $this->assertSame(CommentStatus::Approved->value, $comment->fresh()->status);
    }

    public function test_a_viewer_cannot_change_a_lead_status_from_the_list(): void
    {
        $lead = Lead::factory()->create(['status' => LeadStatus::New->value]);
        $this->actingAs($this->viewer());

        Livewire::test(ListLeads::class)
            ->call('updateTableColumnState', 'status', (string) $lead->getKey(), LeadStatus::Closed->value);
        $this->assertSame(LeadStatus::New->value, $lead->fresh()->status);

        $this->actingAs(tap(User::factory()->create())->assignRole('editor'));
        Livewire::test(ListLeads::class)
            ->call('updateTableColumnState', 'status', (string) $lead->getKey(), LeadStatus::Closed->value);
        $this->assertSame(LeadStatus::Closed->value, $lead->fresh()->status);
    }

    public function test_a_viewer_cannot_duplicate_a_record(): void
    {
        $faq = Faq::factory()->create();
        $viewer = $this->viewer();
        $this->assertFalse($viewer->can('replicate', $faq));
        $this->actingAs($viewer);

        Livewire::test(ListFaqs::class)
            ->assertTableActionHidden('duplicate', $faq)
            ->call('mountTableAction', 'duplicate', (string) $faq->getKey())
            ->call('callMountedTableAction');

        $this->assertSame(1, Faq::count());
        $this->assertTrue(tap(User::factory()->create())->assignRole('editor')->can('replicate', $faq));
    }
}

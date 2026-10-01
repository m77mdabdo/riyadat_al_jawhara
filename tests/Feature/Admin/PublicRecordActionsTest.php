<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\FaqResource\Pages\ListFaqs;
use App\Filament\Resources\ProjectResource\Pages\ListProjects;
use App\Filament\Resources\ServiceResource\Pages\ListServices;
use App\Filament\Resources\StoneTypeResource\Pages\EditStoneType;
use App\Models\Faq;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use App\Models\User;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Shared admin behaviour of records shown on the public site: trash and
 * restore, duplicate, bulk activate, view on site, image alt texts.
 */
class PublicRecordActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
    }

    private function user(string $role): User
    {
        return tap(User::factory()->create())->assignRole($role);
    }

    public function test_deleting_moves_a_record_to_the_trash_and_it_can_be_restored(): void
    {
        $this->actingAs($this->user('admin'));
        $service = Service::factory()->create(['is_active' => true]);

        Livewire::test(ListServices::class)->callTableAction(DeleteAction::class, $service);

        $this->assertSoftDeleted($service);
        $this->get(route('services.show', $service->slug))->assertNotFound();

        Livewire::test(ListServices::class)
            ->filterTable('trashed', true)
            ->assertCanSeeTableRecords([$service->fresh()])
            ->callTableAction('restore', $service->fresh());

        $this->assertNotSoftDeleted($service);
        $this->get(route('services.show', $service->slug))->assertOk();
    }

    public function test_editors_cannot_trash_restore_or_force_delete(): void
    {
        $this->actingAs($this->user('editor'));
        $service = Service::factory()->create();
        $trashed = Service::factory()->create();
        $trashed->delete();

        Livewire::test(ListServices::class)
            ->assertTableActionHidden(DeleteAction::class, $service)
            ->filterTable('trashed', true)
            ->assertTableActionHidden('restore', $trashed)
            ->assertTableActionHidden('forceDelete', $trashed);
    }

    public function test_duplicate_creates_an_inactive_copy_with_a_new_slug(): void
    {
        $this->actingAs($this->user('editor'));
        $project = Project::factory()->create(['title_en' => 'Villa Lobby', 'is_active' => true, 'is_featured' => true]);

        Livewire::test(ListProjects::class)->callTableAction('duplicate', $project);

        $copy = Project::query()->whereKeyNot($project->id)->sole();
        $this->assertSame('Villa Lobby (copy)', $copy->title_en);
        $this->assertFalse($copy->is_active);
        $this->assertFalse($copy->is_featured);
        $this->assertNotSame($project->slug, $copy->slug);
    }

    public function test_bulk_activate_and_deactivate(): void
    {
        $this->actingAs($this->user('editor'));
        $faqs = Faq::factory()->count(2)->create(['is_active' => true]);

        Livewire::test(ListFaqs::class)->callTableBulkAction('deactivate', $faqs);
        $this->assertSame(0, Faq::query()->where('is_active', true)->count());

        Livewire::test(ListFaqs::class)->callTableBulkAction('activate', $faqs);
        $this->assertSame(2, Faq::query()->where('is_active', true)->count());
    }

    public function test_view_on_site_points_to_the_public_page(): void
    {
        $stone = StoneType::factory()->create(['is_active' => true]);
        $draft = Post::factory()->create(['is_published' => false]);

        $this->assertSame(route('stones.show', $stone->slug), $stone->publicUrl());
        $this->assertNull($draft->publicUrl());
    }

    public function test_spec_sheet_can_be_turned_off_per_stone(): void
    {
        $stone = StoneType::factory()->create(['is_active' => true]);
        $this->assertTrue($stone->spec_sheet_enabled);
        $this->get(route('stones.show', $stone->slug))->assertSee(route('stones.spec-sheet', $stone->slug), false);

        $stone->update(['spec_sheet_enabled' => false]);

        $this->get(route('stones.show', $stone->slug))->assertDontSee(route('stones.spec-sheet', $stone->slug), false);
        $this->get(route('stones.spec-sheet', $stone->slug))->assertNotFound();
    }

    public function test_image_alt_texts_are_saved_and_used_on_the_site(): void
    {
        $this->actingAs($this->user('editor'));
        $stone = StoneType::factory()->create(['is_active' => true]);
        $stone->addMedia(UploadedFile::fake()->image('cover.jpg', 800, 800))->toMediaCollection('cover');

        Livewire::test(EditStoneType::class, ['record' => $stone->getRouteKey()])
            ->fillForm(['cover_alt_ar' => 'رخام أبيض مصقول', 'cover_alt_en' => 'Polished white marble'])
            ->call('save')
            ->assertHasNoFormErrors();

        $media = $stone->fresh()->getFirstMedia('cover');
        $this->assertSame('Polished white marble', $media->getCustomProperty('alt_en'));
        $this->assertSame('رخام أبيض مصقول', $media->getCustomProperty('alt_ar'));

        $this->get(route('stones.show', $stone->slug))->assertSee('Polished white marble');
    }
}

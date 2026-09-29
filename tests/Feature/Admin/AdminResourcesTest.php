<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\CommentResource\Pages\ListComments;
use App\Filament\Widgets\LeadsChart;
use App\Filament\Widgets\LeadsOverview;
use App\Filament\Widgets\PendingCommentsWidget;
use App\Models\BlogCategory;
use App\Models\Comment;
use App\Models\Faq;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Slider;
use App\Models\StoneAttribute;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Models\Tag;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Every admin resource: pages load for staff, deletion is admin-only,
 * and people without a panel role are kept out.
 */
class AdminResourcesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: class-string, 2: bool}> slug, model, has create page
     */
    public static function resources(): array
    {
        return [
            'leads' => ['leads', Lead::class, true],
            'projects' => ['projects', Project::class, true],
            'services' => ['services', Service::class, true],
            'stone-categories' => ['stone-categories', StoneCategory::class, true],
            'stone-types' => ['stone-types', StoneType::class, true],
            'stone-attributes' => ['stone-attributes', StoneAttribute::class, true],
            'sliders' => ['sliders', Slider::class, true],
            'testimonials' => ['testimonials', Testimonial::class, true],
            'team-members' => ['team-members', TeamMember::class, true],
            'faqs' => ['faqs', Faq::class, true],
            'posts' => ['posts', Post::class, true],
            'blog-categories' => ['blog-categories', BlogCategory::class, true],
            'tags' => ['tags', Tag::class, true],
            'comments' => ['comments', Comment::class, false],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
    }

    private function user(?string $role): User
    {
        $user = User::factory()->create();

        return $role ? tap($user)->assignRole($role) : $user;
    }

    #[DataProvider('resources')]
    public function test_staff_can_open_index_create_and_edit_pages(string $slug, string $model, bool $hasCreate): void
    {
        $record = $model::factory()->create();

        foreach (['admin', 'editor'] as $role) {
            $this->actingAs($this->user($role));
            $this->get("/admin/{$slug}")->assertOk();
            $this->get("/admin/{$slug}/{$record->getRouteKey()}/edit")->assertOk();
            if ($hasCreate) {
                $this->get("/admin/{$slug}/create")->assertOk();
            }
        }
    }

    #[DataProvider('resources')]
    public function test_only_admins_may_delete(string $slug, string $model): void
    {
        $record = $model::factory()->create();
        $resource = collect(filament()->getPanel('admin')->getResources())->first(fn ($r) => $r::getModel() === $model);

        $this->actingAs($this->user('editor'));
        $this->assertFalse($resource::canDelete($record));
        $this->assertFalse($resource::canDeleteAny());

        $this->actingAs($this->user('admin'));
        $this->assertTrue($resource::canDelete($record));
    }

    public function test_users_without_a_panel_role_are_locked_out(): void
    {
        $this->actingAs($this->user(null));

        $this->get('/admin')->assertForbidden();
        $this->get('/admin/leads')->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/leads')->assertRedirect('/admin/login');
    }

    public function test_dashboard_widgets_render(): void
    {
        Lead::factory()->count(3)->create();
        Comment::factory()->pending()->create();
        $this->actingAs($this->user('admin'));

        // Filament widgets load lazily, so render each one as a component.
        $this->get('/admin')->assertOk();
        Livewire::test(LeadsOverview::class)->assertSee('New Leads')->assertSee('Follow-ups Due')->assertSee('3');
        Livewire::test(PendingCommentsWidget::class)->assertSee('Pending Comments');
        Livewire::test(LeadsChart::class)->assertOk();
    }

    public function test_comment_moderation_actions(): void
    {
        $this->actingAs($this->user('editor'));
        $pending = Comment::factory()->pending()->create();
        $approved = Comment::factory()->create();

        Livewire::test(ListComments::class)
            ->callTableAction('approve', $pending)
            ->callTableAction('spam', $approved);

        $this->assertSame('approved', $pending->fresh()->status);
        $this->assertSame('spam', $approved->fresh()->status);
    }
}

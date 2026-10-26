<?php

namespace Tests\Feature\Admin;

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
use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Arabic admin panel: per-user language, RTL, and no untranslated label on
 * any admin page (every missing lang/ar.json key fails the test by name).
 */
class ArabicAdminTest extends TestCase
{
    use RefreshDatabase;

    private const RESOURCES = [
        'leads' => [Lead::class, ['create', 'edit', 'view']],
        'projects' => [Project::class, ['create', 'edit']],
        'services' => [Service::class, ['create', 'edit']],
        'stone-categories' => [StoneCategory::class, ['create', 'edit']],
        'stone-types' => [StoneType::class, ['create', 'edit']],
        'stone-attributes' => [StoneAttribute::class, ['create', 'edit']],
        'sliders' => [Slider::class, ['create', 'edit']],
        'testimonials' => [Testimonial::class, ['create', 'edit']],
        'team-members' => [TeamMember::class, ['create', 'edit']],
        'faqs' => [Faq::class, ['create', 'edit']],
        'posts' => [Post::class, ['create', 'edit']],
        'blog-categories' => [BlogCategory::class, ['create', 'edit']],
        'tags' => [Tag::class, ['create', 'edit']],
        'comments' => [Comment::class, ['edit']],
        'users' => [User::class, ['create', 'edit']],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
    }

    private function admin(?string $locale): User
    {
        return tap(User::factory()->create(['locale' => $locale]))->assignRole('admin');
    }

    public function test_every_admin_page_is_fully_translated_in_arabic(): void
    {
        $missing = [];
        Lang::handleMissingKeysUsing(function (string $key, array $replace, ?string $locale) use (&$missing) {
            if ($locale === 'ar' && ! str_contains($key, '::') && ! preg_match('/\p{Arabic}/u', $key)) {
                $missing[$key] = true;
            }

            return $key;
        });

        $this->actingAs($this->admin('ar'));

        $urls = ['/admin', '/admin/settings', '/admin/profile', '/admin/activities'];
        foreach (self::RESOURCES as $slug => [$model, $pages]) {
            $record = $model::factory()->create();
            $urls[] = "/admin/{$slug}";
            foreach ($pages as $page) {
                $urls[] = match ($page) {
                    'create' => "/admin/{$slug}/create",
                    'edit' => "/admin/{$slug}/{$record->getRouteKey()}/edit",
                    'view' => "/admin/{$slug}/{$record->getRouteKey()}",
                };
            }
        }
        $urls[] = '/admin/activities/'.Activity::query()->value('id');

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
        Livewire::test(LeadsOverview::class);
        Livewire::test(PendingCommentsWidget::class);
        Livewire::test(LeadsChart::class);

        Lang::handleMissingKeysUsing(null);

        $this->assertSame([], array_keys($missing), 'Missing lang/ar.json keys');
    }

    public function test_no_json_key_collides_with_a_translation_group_file(): void
    {
        // __('Admin') would load lang/en/admin.php (an array) on case-insensitive filesystems.
        $groups = collect(glob(lang_path('*/*.php')))->map(fn ($f) => strtolower(basename($f, '.php')))->unique();
        $keys = collect(array_keys(json_decode(file_get_contents(lang_path('ar.json')), true)))->map(fn ($k) => strtolower($k));

        $this->assertSame([], $keys->intersect($groups)->values()->all());
    }

    public function test_panel_uses_the_user_language_and_rtl(): void
    {
        $this->actingAs($this->admin('ar'));
        $this->get('/admin/leads')->assertOk()->assertSee('dir="rtl"', false)->assertSee('العملاء المحتملون');

        $this->actingAs($this->admin('en'));
        $this->get('/admin/leads')->assertOk()->assertSee('dir="ltr"', false)->assertSee('Leads');
    }

    public function test_relation_labels_follow_the_admin_language(): void
    {
        $service = Service::factory()->create(['name_en' => 'Cladding', 'name_ar' => 'تكسيات']);
        Lead::factory()->create(['service_id' => $service->id]);

        $this->actingAs($this->admin('ar'));
        $this->get('/admin/leads')->assertSee('تكسيات')->assertDontSee('Cladding');

        $this->actingAs($this->admin('en'));
        $this->get('/admin/leads')->assertSee('Cladding');
    }

    public function test_user_can_switch_language_from_the_user_menu(): void
    {
        $user = $this->admin(null);
        $this->actingAs($user);

        $this->get('/admin')->assertSee(route('admin.locale', 'ar'), false);
        $this->from('/admin/leads')->post(route('admin.locale', 'ar'))->assertRedirect('/admin/leads');
        $this->assertSame('ar', $user->fresh()->locale);

        $this->post(route('admin.locale', 'xx'))->assertNotFound();
        $this->from('https://evil.example/')->post(route('admin.locale', 'en'))->assertRedirect(url('/admin'));
    }

    public function test_switch_requires_panel_access(): void
    {
        $this->post(route('admin.locale', 'ar'))->assertForbidden();

        $this->actingAs(User::factory()->create());
        $this->post(route('admin.locale', 'ar'))->assertForbidden();
    }

    public function test_public_site_language_is_not_affected(): void
    {
        $this->actingAs($this->admin('ar'));

        $this->get(route('home', ['locale' => 'en']))->assertOk()->assertSee('lang="en"', false);
    }
}

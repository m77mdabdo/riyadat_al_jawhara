<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Help;
use App\Filament\Resources;
use App\Models\BlogCategory;
use App\Models\Faq;
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
use Database\Seeders\DemoSeeder;
use Filament\Pages\Dashboard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Every content resource: an edit saved in the admin shows on the public
 * page in both languages right away (cached pages are refreshed).
 */
class EditToPublicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: class-string, 1: class-string<Model>, 2: string, 3: \Closure(Model): string}>
     *                                                                                                              edit page, model, bilingual field base, public URL for a locale
     */
    public static function resources(): array
    {
        return [
            'service' => [Resources\ServiceResource\Pages\EditService::class, Service::class, 'name', fn (Model $m) => 'services.show'],
            'stone' => [Resources\StoneTypeResource\Pages\EditStoneType::class, StoneType::class, 'name', fn (Model $m) => 'stones.show'],
            'stone category' => [Resources\StoneCategoryResource\Pages\EditStoneCategory::class, StoneCategory::class, 'name', fn (Model $m) => 'home'],
            'stone attribute' => [Resources\StoneAttributeResource\Pages\EditStoneAttribute::class, StoneAttribute::class, 'name', fn (Model $m) => 'stones.index'],
            'project' => [Resources\ProjectResource\Pages\EditProject::class, Project::class, 'title', fn (Model $m) => 'projects.show'],
            'post' => [Resources\PostResource\Pages\EditPost::class, Post::class, 'title', fn (Model $m) => 'blog.show'],
            'blog category' => [Resources\BlogCategoryResource\Pages\EditBlogCategory::class, BlogCategory::class, 'name', fn (Model $m) => 'blog.index'],
            'tag' => [Resources\TagResource\Pages\EditTag::class, Tag::class, 'name', fn (Model $m) => 'blog.index'],
            'faq' => [Resources\FaqResource\Pages\EditFaq::class, Faq::class, 'question', fn (Model $m) => 'faq'],
            'slider' => [Resources\SliderResource\Pages\EditSlider::class, Slider::class, 'title', fn (Model $m) => 'home'],
            'testimonial' => [Resources\TestimonialResource\Pages\EditTestimonial::class, Testimonial::class, 'comment', fn (Model $m) => 'home'],
            'team member' => [Resources\TeamMemberResource\Pages\EditTeamMember::class, TeamMember::class, 'name', fn (Model $m) => 'about'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);
        Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
    }

    private function url(string $route, Model $record, string $locale): string
    {
        $parameters = ['locale' => $locale];
        if (str_ends_with($route, '.show')) {
            $parameters[] = $record;
        }

        return route($route, $parameters);
    }

    #[DataProvider('resources')]
    public function test_an_edit_shows_on_the_site_in_both_languages(string $page, string $model, string $field, \Closure $route): void
    {
        $record = $model::query()
            ->when(in_array('is_active', (new $model)->getFillable(), true), fn ($q) => $q->where('is_active', true))
            ->when($model === Post::class, fn ($q) => $q->published())
            ->when($model === Project::class, fn ($q) => $q->where('is_featured', true))
            ->firstOrFail();
        $routeName = $route($record);

        // Warm every cache the page uses.
        foreach (['ar', 'en'] as $locale) {
            $this->get($this->url($routeName, $record, $locale))->assertOk();
        }

        $this->actingAs(tap(User::factory()->create())->assignRole('editor'));
        Livewire::test($page, ['record' => $record->getRouteKey()])
            ->fillForm(["{$field}_ar" => 'نص معدّل للاختبار', "{$field}_en" => 'Edited for the test'])
            ->call('save')
            ->assertHasNoFormErrors();
        auth()->logout();

        $record->refresh();
        $this->get($this->url($routeName, $record, 'ar'))->assertOk()->assertSee('نص معدّل للاختبار');
        $this->get($this->url($routeName, $record, 'en'))->assertOk()->assertSee('Edited for the test');
    }

    public function test_a_role_without_permissions_reaches_no_admin_screen(): void
    {
        $this->artisan('app:import-site-content');
        Role::create(['name' => 'Nothing yet', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('Nothing yet'));

        $this->get('/admin')->assertOk();
        foreach (filament()->getPanel('admin')->getResources() as $resource) {
            $this->get($resource::getUrl('index'))->assertForbidden();
        }
        foreach (filament()->getPanel('admin')->getPages() as $page) {
            if (! in_array($page, [Dashboard::class, Help::class], true)) {
                $this->get($page::getUrl())->assertForbidden();
            }
        }
    }
}

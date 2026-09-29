<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_records_are_hidden_and_their_pages_404(): void
    {
        $service = Service::factory()->inactive()->create(['name_en' => 'Hidden Service']);
        $stone = StoneType::factory()->inactive()->create(['name_en' => 'Hidden Stone']);
        $project = Project::factory()->inactive()->create(['title_en' => 'Hidden Project']);

        $this->get(route('services.show', $service))->assertNotFound();
        $this->get(route('stones.show', $stone))->assertNotFound();
        $this->get(route('projects.show', $project))->assertNotFound();

        foreach (['/', route('services.index'), route('stones.index'), route('projects.index')] as $url) {
            $this->get($url)->assertDontSee('Hidden Service')->assertDontSee('Hidden Stone')->assertDontSee('Hidden Project');
        }
    }

    public function test_unknown_slugs_404(): void
    {
        foreach (['services.show', 'stones.show', 'projects.show', 'blog.show'] as $route) {
            $this->get(route($route, 'does-not-exist'))->assertNotFound();
        }
    }

    public function test_home_shows_featured_projects_only(): void
    {
        Project::factory()->featured()->create(['title_en' => 'Featured One']);
        Project::factory()->create(['title_en' => 'Regular One']);

        $this->get('/')->assertSee('Featured One')->assertDontSee('Regular One');
    }

    public function test_project_filters_by_service(): void
    {
        $service = Service::factory()->create();
        Project::factory()->create(['title_en' => 'With Service', 'service_id' => $service->id]);
        Project::factory()->create(['title_en' => 'Other']);

        $this->assertSame(['With Service'], $this->get(route('projects.index', ['service' => $service->slug]))->viewData('projects')->pluck('title_en')->all());
    }

    public function test_stones_filter_by_category(): void
    {
        $marble = StoneCategory::factory()->create();
        StoneType::factory()->create(['name_en' => 'In Category', 'stone_category_id' => $marble->id]);
        StoneType::factory()->create(['name_en' => 'Elsewhere']);

        $this->assertSame(['In Category'], $this->get(route('stones.index', ['stone_category' => $marble->slug]))->viewData('stoneTypes')->pluck('name_en')->all());
    }

    public function test_faq_about_and_testimonials_show_active_items_only(): void
    {
        Faq::factory()->create(['question_en' => 'Visible question?']);
        Faq::factory()->inactive()->create(['question_en' => 'Hidden question?']);
        TeamMember::factory()->create(['name_en' => 'Visible Member']);
        TeamMember::factory()->inactive()->create(['name_en' => 'Hidden Member']);
        Testimonial::factory()->create(['comment_en' => 'Visible review']);
        Testimonial::factory()->inactive()->create(['comment_en' => 'Hidden review']);

        $this->get(route('faq'))->assertSee('Visible question?')->assertDontSee('Hidden question?');
        $this->get(route('about'))->assertSee('Visible Member')->assertDontSee('Hidden Member');
        $this->get('/')->assertSee('Visible review')->assertDontSee('Hidden review');
    }

    public function test_faq_page_empty_state(): void
    {
        $this->get(route('faq'))->assertOk()->assertSee(__('site.faq.no_faqs_yet'));
    }

    public function test_legal_pages_show_content_or_fallback(): void
    {
        $this->get(route('legal.privacy'))->assertOk()->assertSee(__('site.legal.content_missing'));

        Setting::editable()->update(['terms_en' => '<p>Our terms</p>', 'terms_ar' => '<p>الشروط</p>']);
        $this->get(route('legal.terms'))->assertSee('Our terms');
        $this->get('/lang/ar');
        $this->get(route('legal.terms'))->assertSee('الشروط');
    }

    public function test_404_page_is_branded_and_localized(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee(__('site.errors.404_heading'));

        $this->get('/lang/ar');
        $this->get('/no-such-page')->assertNotFound()->assertSee('dir="rtl"', false)->assertSee(__('site.errors.404_heading', [], 'ar'));
    }

    public function test_every_public_page_renders_rtl_arabic(): void
    {
        $this->seed(DemoSeeder::class);
        $this->get('/lang/ar');

        $urls = [
            '/', route('services.index'), route('services.show', Service::first()), route('stones.index'),
            route('stones.show', StoneType::first()), route('projects.index'), route('projects.show', Project::first()),
            route('blog.index'), route('blog.show', Post::published()->first()), route('faq'), route('about'),
            route('contact'), route('legal.privacy'), route('legal.terms'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertOk()->assertSee('<html lang="ar" dir="rtl">', false)->assertSee('font-arabic', false);
        }
    }

    public function test_locale_switch_ignores_unknown_locales(): void
    {
        $this->get('/lang/fr')->assertRedirect();
        $this->get('/')->assertSee('<html lang="en" dir="ltr">', false);
    }
}

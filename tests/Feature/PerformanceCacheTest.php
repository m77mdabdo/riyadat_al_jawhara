<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Support\FragmentCache;
use App\Support\SiteImages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PerformanceCacheTest extends TestCase
{
    use RefreshDatabase;

    private function queriesFor(string $url): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    public function test_home_sections_are_served_from_the_fragment_cache(): void
    {
        Service::factory()->count(3)->create();
        Project::factory()->count(2)->create(['is_featured' => true]);

        $cold = $this->queriesFor(route('home'));
        $warm = $this->queriesFor(route('home'));

        $this->assertLessThan($cold, $warm);
        $this->assertLessThanOrEqual(3, $warm, 'warm home: settings + sliders only');
    }

    public function test_content_changes_bust_the_cached_sections(): void
    {
        $service = Service::factory()->create(['name_en' => 'Old Service Name']);
        $this->get(route('home'))->assertSee('Old Service Name');

        $service->update(['name_en' => 'Fresh Service Name']);
        $this->get(route('home'))->assertSee('Fresh Service Name')->assertDontSee('Old Service Name');

        $service->delete();
        $this->get(route('home'))->assertDontSee('Fresh Service Name');
    }

    public function test_settings_changes_bust_the_cached_sections(): void
    {
        $this->get(route('home'))->assertOk();
        $this->assertNotNull(Cache::get(FragmentCache::VERSION_KEY));

        Setting::editable()->update(['stat_projects_completed' => 321]);

        $this->assertNull(Cache::get(FragmentCache::VERSION_KEY));
        $this->get(route('home'))->assertSee('321');
    }

    public function test_sections_are_cached_per_locale(): void
    {
        Service::factory()->create(['name_en' => 'Cladding', 'name_ar' => 'تكسيات']);

        $this->get(route('home', ['locale' => 'en']))->assertSee('Cladding');
        $this->get(route('home', ['locale' => 'ar']))->assertSee('تكسيات')->assertDontSee('Cladding');
    }

    public function test_blog_view_counter_does_not_flush_the_cache(): void
    {
        $post = Post::factory()->create();
        $this->get(route('home'))->assertOk();
        $version = Cache::get(FragmentCache::VERSION_KEY);

        $this->get(route('blog.show', $post))->assertOk();

        $this->assertSame($version, Cache::get(FragmentCache::VERSION_KEY));
    }

    public function test_sitemap_is_cached_and_busted_by_content_changes(): void
    {
        $project = Project::factory()->create(['slug' => 'first-project']);
        $this->get('/sitemap.xml')->assertSee('first-project');

        $cold = $this->queriesFor('/sitemap.xml');
        $this->assertLessThanOrEqual(1, $cold);

        $project->update(['slug' => 'renamed-project']);
        $this->get('/sitemap.xml')->assertSee('renamed-project')->assertDontSee('first-project');
    }

    public function test_pages_with_forms_are_not_fragment_cached(): void
    {
        $first = $this->get(route('contact'))->getContent();
        $this->flushSession();
        $second = $this->get(route('contact'))->getContent();

        preg_match('/name="_token" value="([^"]+)"/', $first, $a);
        preg_match('/name="_token" value="([^"]+)"/', $second, $b);
        $this->assertNotSame($a[1] ?? null, $b[1] ?? null, 'each session gets its own CSRF token');
    }

    public function test_lcp_images_are_preloaded_and_responsive(): void
    {
        $this->assertNotNull(SiteImages::srcset('hero'), 'variants committed in public/images/site');

        $this->get(route('home'))
            ->assertSee('<link rel="preload" as="image"', false)
            ->assertSee('imagesrcset="', false)
            ->assertSee('hero-640.webp', false);

        $this->get(route('stones.index'))
            ->assertSee('rel="preload" as="image"', false)
            ->assertSee('texture-quartz-640.webp', false)
            ->assertSee('sizes="100vw"', false);
    }

    public function test_variants_command_writes_smaller_renditions(): void
    {
        $tmp = sys_get_temp_dir().'/jra-variants-'.uniqid();
        File::ensureDirectoryExists("{$tmp}/public/images/site");
        $this->app->usePublicPath("{$tmp}/public");
        imagewebp(imagecreatetruecolor(1600, 900), "{$tmp}/public/images/site/hero.webp");
        imagewebp(imagecreatetruecolor(800, 600), "{$tmp}/public/images/site/about.webp");

        $this->artisan('site-images:variants')->assertSuccessful();

        $this->assertSame(640, getimagesize("{$tmp}/public/images/site/hero-640.webp")[0]);
        $this->assertSame(1024, getimagesize("{$tmp}/public/images/site/hero-1024.webp")[0]);
        $this->assertFileExists("{$tmp}/public/images/site/about-640.webp");
        $this->assertFileDoesNotExist("{$tmp}/public/images/site/about-1024.webp", 'never upscale');
        $this->assertStringContainsString('about-640.webp', SiteImages::srcset('about'));
        $this->assertStringEndsWith(' 800w', SiteImages::srcset('about'));

        File::deleteDirectory($tmp);
    }
}

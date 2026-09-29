<?php

namespace Tests\Feature;

use App\Filament\Pages\Settings;
use App\Models\Project;
use App\Models\Setting;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Models\User;
use App\Support\SiteImages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Phase 10: design system, homepage enrichment, imagery guarantees.
 */
class VisualSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_chosen_image_exists_and_meets_its_budget(): void
    {
        $choices = SiteImages::choices();
        $slots = config('site-images.slots');
        $this->assertNotEmpty($choices);

        foreach ($choices as $slot => $choice) {
            $path = SiteImages::path($slot);
            $this->assertFileExists($path, $slot);
            [$width] = getimagesize($path);

            $this->assertSame('image/webp', mime_content_type($path), $slot);
            $this->assertLessThanOrEqual($slots[$slot]['max_width'] ?? 1920, $width, $slot);
            $this->assertLessThanOrEqual($slots[$slot]['max_kb'] * 1024, filesize($path), $slot);
            $this->assertContains($choice['source'], ['Pexels', 'Pixabay'], $slot);
        }

        foreach (['texture-marble', 'texture-granite', 'texture-sandstone', 'texture-quartz'] as $texture) {
            $this->assertLessThanOrEqual(120 * 1024, filesize(SiteImages::path($texture)), $texture);
        }
        $this->assertLessThanOrEqual(350 * 1024, filesize(SiteImages::path('hero')));
    }

    public function test_every_chosen_image_is_credited(): void
    {
        $credits = file_get_contents(config('site-images.credits_path'));

        foreach (SiteImages::choices() as $slot => $choice) {
            $this->assertStringContainsString("public/images/site/{$slot}.webp", $credits);
            $this->assertStringContainsString($choice['id'], $credits);
            $this->assertStringContainsString($choice['license_url'], $credits);
        }
    }

    public function test_missing_slot_falls_back_instead_of_breaking(): void
    {
        $this->assertSame('fallback.webp', SiteImages::url('does-not-exist', 'fallback.webp'));
        $this->assertNull(SiteImages::url('heritage-diriyah'));
        $this->assertStringContainsString('images/site/hero.webp?v=', SiteImages::url('hero'));
    }

    public function test_fonts_are_self_hosted_and_the_csp_no_longer_allows_google_fonts(): void
    {
        $response = $this->get(route('home'));

        $response->assertDontSee('fonts.googleapis.com', false)->assertDontSee('fonts.gstatic.com', false);
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringNotContainsString('googleapis', $csp);
        $this->assertStringNotContainsString('gstatic', $csp);

        $css = file_get_contents(resource_path('css/app.css'));
        foreach (['@fontsource-variable/cairo', '@fontsource-variable/inter', '@fontsource/reem-kufi', '@fontsource/marcellus'] as $font) {
            $this->assertStringContainsString($font, $css);
        }
    }

    public function test_home_has_the_new_sections(): void
    {
        $category = StoneCategory::factory()->create(['name_en' => 'Marble', 'slug' => 'marble']);
        StoneType::factory()->count(2)->create(['stone_category_id' => $category->id]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(__('site.home.why_choose_us'))
            ->assertSee(__('site.home.why_items')[0]['title'])
            ->assertSee(route('stones.index', ['stone_category' => 'marble']), false)
            ->assertSee('2 stones')
            ->assertSee(__('site.home.heritage_title'))
            ->assertSee(__('site.home.heritage_note'))
            ->assertSee(SiteImages::url('heritage-jeddah'), false);
    }

    public function test_heritage_places_without_a_verified_photo_show_a_pattern_card(): void
    {
        $html = $this->get(route('home'))->getContent();

        $this->assertStringContainsString(__('site.home.heritage_items.diriyah.name'), $html);
        $this->assertStringNotContainsString('images/site/heritage-diriyah', $html);
    }

    public function test_stats_are_hidden_until_the_company_enters_real_figures(): void
    {
        $this->get(route('home'))->assertDontSee(__('site.home.stats_title'));

        Setting::editable()->update(['stat_projects_completed' => 120, 'stat_years_experience' => null, 'stat_cities_served' => 0]);

        $this->get(route('home'))
            ->assertSee(__('site.home.stats_title'))
            ->assertSee(__('site.home.stats.projects'))
            ->assertSee('120')
            ->assertDontSee(__('site.home.stats.years'))
            ->assertDontSee(__('site.home.stats.cities'));
    }

    public function test_hero_offers_quote_and_whatsapp_ctas(): void
    {
        Setting::editable()->update(['whatsapp' => '966500000000']);

        $this->get(route('home'))
            ->assertSee(route('contact'), false)
            ->assertSee('https://wa.me/966500000000', false);

        Setting::editable()->update(['whatsapp' => null]);
        $this->get(route('home'))->assertDontSee('wa.me/', false);
    }

    public function test_inner_pages_use_the_branded_page_header(): void
    {
        foreach (['services.index', 'stones.index', 'projects.index', 'blog.index', 'faq', 'about', 'contact'] as $route) {
            $html = $this->get(route($route))->assertOk()->getContent();

            $this->assertMatchesRegularExpression('#<section class="relative overflow-hidden bg-sand-100[^"]*">\s*<img src="[^"]*images/site/texture-[a-z]+\.webp#', $html, $route);
            $this->assertSame(1, substr_count($html, '<h1'), $route);
        }
    }

    public function test_motifs_are_decorative_and_mirror_safely(): void
    {
        Project::factory()->featured()->create();
        $html = $this->get(route('home'))->getContent();

        preg_match_all('/<svg[^>]*class="[^"]*(?:h-2\.5 w-20|h-3 flex-1)[^"]*"[^>]*>/', $html, $bands);
        $this->assertNotEmpty($bands[0]);
        foreach ($bands[0] as $svg) {
            $this->assertMatchesRegularExpression('/aria-hidden="true"|focusable="false"/', $svg);
        }
        $this->assertStringContainsString('mashrabiya-frame', $html);

        // Symmetric shapes only: no directional transforms needed for RTL.
        foreach (glob(resource_path('views/components/motif/*.blade.php')) as $motif) {
            $this->assertStringNotContainsString('scaleX(-1)', file_get_contents($motif));
        }
    }

    public function test_pick_and_review_commands_write_to_configured_paths(): void
    {
        $tmp = sys_get_temp_dir().'/jra-images-'.uniqid();
        File::ensureDirectoryExists("{$tmp}/storage/app/stock-candidates/hero");
        $this->app->useStoragePath("{$tmp}/storage");
        $this->app->usePublicPath("{$tmp}/public");
        config([
            'site-images.state_path' => "{$tmp}/state.json",
            'site-images.credits_path' => "{$tmp}/CREDITS.md",
            'site-images.review_path' => "{$tmp}/review/index.html",
        ]);

        $image = imagecreatetruecolor(40, 30);
        imagewebp($image, "{$tmp}/storage/app/stock-candidates/hero/pexels-1.webp");
        File::put("{$tmp}/storage/app/stock-candidates/hero/candidates.json", json_encode([[
            'id' => 'pexels-1', 'source' => 'Pexels', 'photographer' => 'Someone', 'photographer_url' => 'https://example.test/p',
            'page_url' => 'https://example.test/photo', 'license_url' => 'https://www.pexels.com/license/', 'score' => 0.9,
            'brightness' => 0.6, 'warmth' => 0.1, 'kb' => 1, 'saved_width' => 40, 'saved_height' => 30, 'file' => 'hero/pexels-1.webp', 'alt' => 'x',
        ]]));

        $this->artisan('site-images:pick', ['slot' => 'hero'])->assertSuccessful();
        $this->artisan('site-images:review')->assertSuccessful();

        $this->assertFileExists("{$tmp}/public/images/site/hero.webp");
        $this->assertSame('pexels-1', json_decode(file_get_contents("{$tmp}/state.json"), true)['hero']['id']);
        $this->assertStringContainsString('[Someone](https://example.test/p)', file_get_contents("{$tmp}/CREDITS.md"));
        $this->assertStringContainsString('site-images:pick hero pexels-1', file_get_contents("{$tmp}/review/index.html"));

        File::deleteDirectory($tmp);
    }

    public function test_admin_can_save_stats_and_rejects_invalid_values(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));

        Livewire::test(Settings::class)
            ->set('data.stat_years_experience', -3)
            ->call('save')
            ->assertHasErrors(['data.stat_years_experience']);

        Livewire::test(Settings::class)
            ->set('data.stat_years_experience', 15)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(15, (int) Setting::current()->stat_years_experience);
    }
}

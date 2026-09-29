<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Slider;
use App\Models\StoneCategory;
use App\Models\StoneType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Guards the Phase 4 UI/accessibility work at the markup level
 * (visual checks were done separately with Playwright screenshots).
 */
class FrontendMarkupTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_has_faq_link_and_accessible_menu_button(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        // Desktop nav, mobile nav and footer.
        $this->assertSame(3, substr_count($html, 'href="'.route('faq').'"'));
        $this->assertStringContainsString('aria-controls="mobile-menu"', $html);
        $this->assertStringContainsString(':aria-expanded="open.toString()"', $html);
        $this->assertStringContainsString('id="mobile-menu"', $html);
        $this->assertStringContainsString('@keydown.escape.window="close()"', $html);
    }

    public function test_active_nav_state_covers_child_routes(): void
    {
        $service = Service::create(['name_en' => 'Supply', 'name_ar' => 'توريد', 'is_active' => true]);

        $html = $this->get(route('services.show', $service))->getContent();

        $this->assertMatchesRegularExpression('#href="'.preg_quote(route('services.index'), '#').'"\s+aria-current="page"#', $html);
    }

    public function test_manifest_is_linked_and_valid(): void
    {
        $this->get(route('home'))->assertSee('<link rel="manifest" href="'.asset('site.webmanifest').'">', false);

        $manifest = json_decode(file_get_contents(public_path('site.webmanifest')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
    }

    public function test_every_static_image_referenced_in_views_exists(): void
    {
        $views = collect(glob(resource_path('views/{,*/,*/*/}*.blade.php'), GLOB_BRACE))->map(fn ($f) => file_get_contents($f))->implode("\n");
        preg_match_all("/asset\('([^']+\.(?:png|ico|webp|jpg|mp4|webmanifest))'\)/", $views, $matches);

        $this->assertNotEmpty($matches[1]);
        foreach (array_unique($matches[1]) as $path) {
            $this->assertFileExists(public_path($path), $path);
        }
    }

    public function test_items_without_images_render_placeholders_not_broken_images(): void
    {
        Service::create(['name_en' => 'Supply', 'name_ar' => 'توريد', 'is_active' => true]);
        $category = StoneCategory::create(['name_en' => 'Marble', 'name_ar' => 'رخام']);
        $stone = StoneType::create(['stone_category_id' => $category->id, 'name_en' => 'Carrara', 'name_ar' => 'كرارا', 'is_active' => true]);
        $project = Project::create(['title_en' => 'Villa', 'title_ar' => 'فيلا', 'is_active' => true, 'is_featured' => true]);

        foreach ([route('home'), route('services.index'), route('stones.index'), route('stones.show', $stone), route('projects.index'), route('projects.show', $project)] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('src=""', $html, $url);
            $this->assertStringContainsString('JRA</span>', $html, $url); // placeholder badge
        }

        // Stone preview panel must branch on the image instead of binding :src to null.
        $this->get(route('stones.index'))->assertSee('<template x-if="! stone.image">', false);
    }

    public function test_images_have_dimensions_and_async_decoding(): void
    {
        $html = $this->get(route('about'))->getContent();

        preg_match_all('/<img\b[^>]*>/', $html, $images);
        $this->assertNotEmpty($images[0]);
        foreach ($images[0] as $img) {
            $this->assertMatchesRegularExpression('/\bwidth="\d+"/', $img, $img);
            $this->assertMatchesRegularExpression('/\bheight="\d+"/', $img, $img);
            $this->assertStringContainsString('decoding="async"', $img);
            $this->assertMatchesRegularExpression('/\balt="/', $img);
        }
    }

    public function test_hero_single_slide_has_no_controls_and_multiple_slides_get_pause(): void
    {
        Slider::create(['title_en' => 'One', 'title_ar' => 'واحد', 'is_active' => true]);
        $this->get(route('home'))->assertDontSee(__('site.common.pause_slideshow'))->assertSee('count: 1', false);

        Slider::create(['title_en' => 'Two', 'title_ar' => 'اثنان', 'is_active' => true]);
        $html = $this->get(route('home'))->assertSee('count: 2', false)->getContent();
        $this->assertStringContainsString(__('site.common.pause_slideshow'), $html);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $html);
        $this->assertSame(1, substr_count($html, '<h1'), 'exactly one h1 on the page');
    }

    public function test_lightbox_is_an_accessible_dialog(): void
    {
        $category = StoneCategory::create(['name_en' => 'Marble', 'name_ar' => 'رخام']);
        $stone = StoneType::create(['stone_category_id' => $category->id, 'name_en' => 'Carrara', 'name_ar' => 'كرارا', 'is_active' => true]);
        Storage::fake('public');
        $stone->addMedia(UploadedFile::fake()->image('g1.jpg', 800, 600))->toMediaCollection('gallery');
        $stone->addMedia(UploadedFile::fake()->image('g2.jpg', 800, 600))->toMediaCollection('gallery');

        $html = $this->get(route('stones.show', $stone))->assertOk()->getContent();
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('aria-modal="true"', $html);
        $this->assertStringContainsString('@keydown.tab="trap($event)"', $html);
        $this->assertStringContainsString('this.opener?.focus()', $html);
    }

    public function test_pagination_is_branded_translated_and_rtl_safe(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Project::create(['title_en' => "P{$i}", 'title_ar' => "م{$i}", 'is_active' => true, 'order' => $i]);
        }

        $html = $this->get(route('projects.index'))->getContent();
        $this->assertStringContainsString(__('site.pagination.summary', ['from' => 1, 'to' => 9, 'total' => 12]), $html);
        $this->assertStringContainsString('rel="next"', $html);
        $this->assertStringNotContainsString('dark:bg-gray-800', $html);
        $this->assertStringContainsString('rtl:rotate-180', $html);

        $this->useLocale('ar');
        $this->get(route('projects.index'))->assertSee('عرض 1–9 من أصل 12')->assertDontSee('&raquo;', false);
    }

    public function test_comment_form_only_refills_the_form_that_was_submitted(): void
    {
        $post = Post::create(['title_en' => 'P', 'title_ar' => 'م', 'is_published' => true, 'published_at' => now()->subDay()]);
        $parent = Comment::create(['post_id' => $post->id, 'name' => 'A', 'email' => 'a@example.test', 'body' => 'Root', 'status' => 'approved']);

        $this->from(route('blog.show', $post))
            ->post(route('blog.comments.store', $post), ['parent_id' => $parent->id, 'name' => 'Replier Name', 'email' => 'bad', 'body' => 'x'])
            ->assertSessionHasErrors('email');

        $html = $this->get(route('blog.show', $post))->getContent();
        $this->assertSame(1, substr_count($html, 'value="Replier Name"'));
        $this->assertStringContainsString('id="email-website-'.$parent->id.'-error"', $html);
        $this->assertStringNotContainsString('id="email-website-root-error"', $html);
        $this->assertStringContainsString('replyTo: '.$parent->id, $html);
    }

    public function test_contact_errors_are_announced_and_highlighted(): void
    {
        $this->from(route('contact'))->post(route('contact.store'), []);

        $html = $this->get(route('contact'))->getContent();
        $this->assertStringContainsString('aria-invalid="true" aria-describedby="name-error"', $html);
        $this->assertStringContainsString('id="name-error"', $html);
        $this->assertMatchesRegularExpression('/name="name"[^>]*border-red-400/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="name"[^>]*border-stone-300/', $html);
    }

    public function test_no_translation_is_injected_into_alpine_as_a_raw_string(): void
    {
        foreach (glob(resource_path('views/{,*/,*/*/}*.blade.php'), GLOB_BRACE) as $file) {
            $this->assertDoesNotMatchRegularExpression("/x-text=\"[^\"]*'\{\{/", file_get_contents($file), basename($file));
        }
    }
}

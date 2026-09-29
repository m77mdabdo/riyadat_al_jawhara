<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private function jsonLd(string $html, string $type): ?array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $blocks);
        foreach ($blocks[1] as $block) {
            $data = json_decode($block, true);
            if (($data['@type'] ?? null) === $type) {
                return $data;
            }
        }

        return null;
    }

    public function test_canonical_and_og_url_drop_filters_but_keep_pagination(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            Project::create(['title_en' => "P{$i}", 'title_ar' => "م{$i}", 'is_active' => true, 'sector' => 'residential']);
        }

        $base = route('projects.index');
        $this->get($base.'?sector=residential&utm_source=x')
            ->assertSee('<link rel="canonical" href="'.$base.'">', false)
            ->assertSee('<meta property="og:url" content="'.$base.'">', false);

        $this->get($base.'?page=2&sector=residential')->assertSee('<link rel="canonical" href="'.$base.'?page=2">', false);
        $this->get($base.'?page=1')->assertSee('<link rel="canonical" href="'.$base.'">', false);
    }

    public function test_entity_pages_use_their_own_share_image_and_clean_description(): void
    {
        $project = Project::create([
            'title_en' => 'Villa Lobby', 'title_ar' => 'ردهة', 'is_active' => true,
            'description_en' => "<b>Marble</b>\n\n lobby   ".str_repeat('word ', 60),
        ]);
        $project->addMedia(UploadedFile::fake()->image('lobby.jpg', 1600, 900))->toMediaCollection('cover');

        $html = $this->get(route('projects.show', $project))->getContent();

        $cover = $project->fresh()->coverImageUrl();
        $this->assertStringContainsString('<meta property="og:image" content="'.$cover.'">', $html);
        $this->assertStringContainsString('<meta name="twitter:image" content="'.$cover.'">', $html);
        $this->assertStringContainsString('<meta name="twitter:title" content="Villa Lobby">', $html);
        preg_match('/<meta name="description" content="([^"]*)"/', $html, $m);
        $this->assertLessThanOrEqual(163, mb_strlen(html_entity_decode($m[1])));
        $this->assertStringStartsWith('Marble lobby word', html_entity_decode($m[1]));
    }

    public function test_pages_without_image_use_the_default_share_image(): void
    {
        $this->get(route('faq'))->assertSee('<meta property="og:image" content="'.asset(Setting::DEFAULT_OG_IMAGE).'">', false);
    }

    public function test_blog_post_is_an_article_with_times_and_breadcrumbs(): void
    {
        $post = Post::create(['title_en' => 'Care Guide', 'title_ar' => 'دليل', 'body_en' => '<p>Body text</p>', 'is_published' => true, 'published_at' => now()->subDay()]);

        $html = $this->get(route('blog.show', $post))->getContent();

        $this->assertStringContainsString('<meta property="og:type" content="article">', $html);
        $this->assertStringContainsString('<meta property="article:published_time"', $html);
        $crumbs = $this->jsonLd($html, 'BreadcrumbList');
        $this->assertSame(['Home', 'Blog', 'Care Guide'], array_column($crumbs['itemListElement'], 'name'));
        $this->assertSame(route('blog.show', $post), end($crumbs['itemListElement'])['item']);
        $this->assertNotNull($this->jsonLd($html, 'Article'));
    }

    public function test_breadcrumbs_are_localized(): void
    {
        $this->get('/lang/ar');
        $crumbs = $this->jsonLd($this->get(route('stones.index'))->getContent(), 'BreadcrumbList');

        $this->assertSame([__('site.nav.home', [], 'ar'), __('site.stones.title', [], 'ar')], array_column($crumbs['itemListElement'], 'name'));
    }

    public function test_faq_page_has_faqpage_schema(): void
    {
        Faq::create(['question_en' => 'Do you install?', 'question_ar' => 'هل تركبون؟', 'answer_en' => 'Yes, <b>always</b>.', 'answer_ar' => 'نعم', 'is_active' => true]);
        Faq::create(['question_en' => 'Hidden?', 'question_ar' => 'مخفي', 'answer_en' => 'x', 'answer_ar' => 'x', 'is_active' => false]);

        $schema = $this->jsonLd($this->get(route('faq'))->getContent(), 'FAQPage');

        $this->assertCount(1, $schema['mainEntity']);
        $this->assertSame('Do you install?', $schema['mainEntity'][0]['name']);
    }

    public function test_blog_search_results_are_noindex(): void
    {
        $this->get(route('blog.index', ['q' => 'marble']))->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get(route('blog.index'))->assertDontSee('name="robots"', false);
    }

    public function test_sitemap_is_complete_with_lastmod(): void
    {
        $project = Project::create(['title_en' => 'Villa', 'title_ar' => 'فيلا', 'is_active' => true]);
        Project::create(['title_en' => 'Draft', 'title_ar' => 'مسودة', 'is_active' => false]);
        Post::create(['title_en' => 'Future', 'title_ar' => 'مستقبل', 'is_published' => true, 'published_at' => now()->addWeek()]);

        $response = $this->get(route('sitemap'))->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = simplexml_load_string($response->getContent());
        $locs = array_map('strval', $xml->xpath('//*[local-name()="loc"]'));

        foreach (['faq', 'legal.privacy', 'legal.terms', 'about', 'contact', 'home'] as $route) {
            $this->assertContains(route($route), $locs, $route);
        }
        $this->assertContains(route('projects.show', $project), $locs);
        $this->assertNotContains(route('projects.show', 'draft'), $locs);
        $this->assertNotContains(route('blog.show', 'future'), $locs);
        $this->assertStringContainsString('<lastmod>'.$project->updated_at->toAtomString().'</lastmod>', $response->getContent());
    }

    public function test_robots_rules(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /lang/')
            ->assertSee('Sitemap: '.route('sitemap'));

        $static = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Disallow: /admin', $static);
        $this->assertStringContainsString('Disallow: /lang/', $static);
    }

    public function test_language_switch_links_are_nofollow(): void
    {
        $this->get('/')->assertSee('hreflang="ar" lang="ar" rel="nofollow"', false);
    }
}

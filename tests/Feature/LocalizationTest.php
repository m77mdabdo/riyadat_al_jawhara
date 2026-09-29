<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * URL-based languages: /ar/... and /en/..., Arabic default, 301 from legacy
 * un-prefixed URLs, hreflang/canonical per locale, sitemap alternates.
 */
class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public static function legacyRedirects(): array
    {
        return [
            'services' => ['/services', '/ar/services'],
            'stones with filter' => ['/stones?finish=polished&colour=white', '/ar/stones?finish=polished&colour=white'],
            'projects paginated' => ['/projects?page=2', '/ar/projects?page=2'],
            'blog search' => ['/blog?q=marble', '/ar/blog?q=marble'],
            'faq' => ['/faq', '/ar/faq'],
            'about' => ['/about', '/ar/about'],
            'contact with context' => ['/contact?service=supply', '/ar/contact?service=supply'],
            'privacy' => ['/privacy-policy', '/ar/privacy-policy'],
            'terms' => ['/terms-of-use', '/ar/terms-of-use'],
        ];
    }

    public function test_root_redirects_permanently_to_arabic(): void
    {
        $this->get('/')->assertStatus(301)->assertRedirect('/ar');
    }

    #[DataProvider('legacyRedirects')]
    public function test_legacy_urls_301_to_arabic_with_query_preserved(string $from, string $to): void
    {
        $this->get($from)->assertStatus(301)->assertRedirect($to);
    }

    public function test_legacy_detail_urls_redirect_too(): void
    {
        $service = Service::factory()->create(['slug' => 'stone-supply']);
        $post = Post::factory()->create(['slug' => 'marble-care']);

        $this->get('/services/stone-supply')->assertStatus(301)->assertRedirect('/ar/services/stone-supply');
        $this->get('/blog/marble-care?utm=x')->assertStatus(301)->assertRedirect('/ar/blog/marble-care?utm=x');
    }

    public function test_unknown_urls_404_instead_of_redirecting(): void
    {
        $this->get('/definitely-not-a-page')->assertNotFound()->assertSee('dir="rtl"', false);
        $this->get('/ar/definitely-not-a-page')->assertNotFound()->assertSee('<html lang="ar" dir="rtl">', false);
        $this->get('/en/definitely-not-a-page')->assertNotFound()->assertSee('<html lang="en" dir="ltr">', false)
            ->assertSee(__('site.errors.404_heading', [], 'en'));
    }

    public function test_unknown_slug_404_renders_in_the_url_language(): void
    {
        $this->get('/en/projects/nope')->assertNotFound()->assertSee('<html lang="en" dir="ltr">', false);
        $this->get('/ar/projects/nope')->assertNotFound()->assertSee('<html lang="ar" dir="rtl">', false);
    }

    public function test_no_accept_language_or_cookie_redirects(): void
    {
        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/')->assertRedirect('/ar');
        $this->withHeader('Accept-Language', 'en-US')->get('/ar/faq')->assertOk()->assertSee('<html lang="ar" dir="rtl">', false);
        $this->withCookie('locale', 'en')->get('/ar/about')->assertOk()->assertSee('<html lang="ar" dir="rtl">', false);
    }

    public function test_html_lang_dir_and_og_locale_follow_the_url(): void
    {
        $this->get('/ar/faq')
            ->assertSee('<html lang="ar" dir="rtl">', false)
            ->assertSee('<meta property="og:locale" content="ar_SA">', false)
            ->assertSee('<meta property="og:locale:alternate" content="en_US">', false);

        $this->get('/en/faq')
            ->assertSee('<html lang="en" dir="ltr">', false)
            ->assertSee('<meta property="og:locale" content="en_US">', false)
            ->assertSee('<meta property="og:locale:alternate" content="ar_SA">', false);
    }

    public function test_hreflang_and_canonical_on_every_page_type(): void
    {
        $project = Project::factory()->create(['slug' => 'villa']);

        foreach (['/ar/projects/villa', '/en/projects/villa'] as $url) {
            $locale = substr($url, 1, 2);
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('<link rel="canonical" href="'.url($url).'">', $html);
            $this->assertStringContainsString('<link rel="alternate" hreflang="ar" href="'.url('/ar/projects/villa').'">', $html);
            $this->assertStringContainsString('<link rel="alternate" hreflang="en" href="'.url('/en/projects/villa').'">', $html);
            $this->assertStringContainsString('<link rel="alternate" hreflang="x-default" href="'.url('/ar/projects/villa').'">', $html);
            $this->assertStringContainsString('<meta property="og:url" content="'.url($url).'">', $html);
        }
    }

    public function test_canonical_and_hreflang_drop_filters_but_keep_pagination(): void
    {
        Project::factory()->count(12)->create(['sector' => 'residential']);

        $this->get('/en/projects?page=2&sector=residential')
            ->assertSee('<link rel="canonical" href="'.url('/en/projects?page=2').'">', false)
            ->assertSee('<link rel="alternate" hreflang="ar" href="'.url('/ar/projects?page=2').'">', false);
    }

    public function test_switcher_links_to_the_same_page_and_query_in_the_other_language(): void
    {
        $service = Service::factory()->create(['slug' => 'supply']);

        $this->get('/ar/services/supply')
            ->assertSee('href="'.url('/en/services/supply').'" hreflang="en"', false)
            ->assertSee('href="'.url('/ar/services/supply').'" hreflang="ar"', false);

        $this->get('/en/blog?q=marble&category=care')
            ->assertSee('href="'.url('/ar/blog?q=marble&amp;category=care').'" hreflang="ar"', false);
    }

    public function test_legacy_lang_switch_redirects_to_new_style_urls(): void
    {
        $this->withHeader('referer', url('/en/stones?finish=honed'))->get('/lang/ar')->assertRedirect('/ar/stones?finish=honed');
        $this->withHeader('referer', url('/ar/about'))->get('/lang/en')->assertRedirect('/en/about');
        $this->flushHeaders()->get('/lang/en')->assertRedirect('/en');
        $this->withHeader('referer', 'https://evil.example/en/about')->get('/lang/en')->assertRedirect('/en');
        $this->flushHeaders()->get('/lang/xx')->assertRedirect('/ar');
    }

    public function test_internal_links_keep_the_current_language(): void
    {
        Service::factory()->create(['slug' => 'supply']);

        $html = $this->get('/en/services')->getContent();

        $this->assertStringContainsString('href="'.url('/en/services/supply').'"', $html);
        $this->assertStringContainsString('href="'.url('/en/contact').'"', $html);
        $this->assertStringNotContainsString('href="'.url('/ar/contact').'" class', $html);
    }

    public function test_sitemap_lists_both_languages_with_alternates(): void
    {
        Project::factory()->create(['slug' => 'villa']);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $doc = simplexml_load_string($xml);
        $doc->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $doc->registerXPathNamespace('x', 'http://www.w3.org/1999/xhtml');
        $locs = array_map('strval', $doc->xpath('//s:loc'));

        $this->assertContains(url('/ar/projects/villa'), $locs);
        $this->assertContains(url('/en/projects/villa'), $locs);
        $this->assertContains(url('/ar'), $locs);
        $this->assertContains(url('/en'), $locs);

        $alternates = $doc->xpath('//s:url[s:loc="'.url('/en/projects/villa').'"]/x:link');
        $map = collect($alternates)->mapWithKeys(fn ($l) => [(string) $l['hreflang'] => (string) $l['href']]);
        $this->assertSame(url('/ar/projects/villa'), $map['ar']);
        $this->assertSame(url('/en/projects/villa'), $map['en']);
        $this->assertSame(url('/ar/projects/villa'), $map['x-default']);
    }

    public function test_contact_form_works_under_both_languages(): void
    {
        foreach (['ar', 'en'] as $locale) {
            $this->from("/{$locale}/contact")
                ->post("/{$locale}/contact", ['name' => "Lead {$locale}", 'phone' => '0500000000', 'message' => 'Quote', 'consent' => '1'])
                ->assertRedirect("/{$locale}/contact")
                ->assertSessionHas('success', __('site.contact.success_message', [], $locale));
        }

        $this->assertSame(['ar', 'en'], Lead::orderBy('id')->pluck('locale')->all());
        $this->post('/ar/contact', ['name' => 'Bot', 'phone' => '0500000000', 'message' => 'x', 'consent' => '1', 'website' => 'spam']);
        $this->assertSame(2, Lead::count(), 'honeypot still active under /ar');
    }

    public function test_comment_form_works_under_both_languages(): void
    {
        $post = Post::factory()->create(['slug' => 'care']);

        $this->post('/en/blog/care/comments', ['name' => 'R', 'email' => 'r@example.test', 'body' => 'Hi'])->assertSessionHas('success');
        $this->post('/ar/blog/care/comments', ['name' => 'R', 'email' => 'r@example.test', 'body' => 'مرحبا'])->assertSessionHas('success');
        $this->assertSame(2, $post->comments()->count());
    }

    public function test_contact_throttle_is_shared_across_languages(): void
    {
        $data = ['name' => 'X', 'phone' => '0500000000', 'message' => 'm', 'consent' => '1'];
        for ($i = 0; $i < 5; $i++) {
            $this->post($i % 2 ? '/en/contact' : '/ar/contact', $data);
        }

        $this->from('/en/contact')->post('/en/contact', $data)->assertSessionHas('error');
        $this->assertSame(5, Lead::count());
    }

    public function test_admin_and_unprefixed_endpoints_are_unaffected(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));

        $this->get('/admin')->assertOk()->assertSee('lang="en"', false);
        $this->get('/admin/leads')->assertOk();
        $this->get('/up')->assertOk();
        $this->get('/robots.txt')->assertOk();
        $this->get('/sitemap.xml')->assertOk();
        $this->get('/ar/admin')->assertNotFound();
    }

    public function test_generated_urls_outside_requests_use_the_default_language(): void
    {
        // e.g. queued mail / CLI: no request, no prefix middleware.
        $this->assertStringEndsWith('/ar/contact', route('contact', ['locale' => config('locales.default')]));
        $this->assertSame('ar', config('locales.default'));
    }
}

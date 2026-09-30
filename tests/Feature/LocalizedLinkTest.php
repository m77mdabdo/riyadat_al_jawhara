<?php

namespace Tests\Feature;

use App\Models\Slider;
use App\Support\LocalizedUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LocalizedLinkTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: ?string, 1: ?string}>
     */
    public static function links(): array
    {
        $base = '{base}';

        return [
            'internal path' => ['/contact', "{$base}/ar/contact"],
            'nested path + query + fragment' => ['/projects/villa?x=1#top', "{$base}/ar/projects/villa?x=1#top"],
            'trailing slash' => ['/services/', "{$base}/ar/services"],
            'site root' => ['/', "{$base}/ar"],
            'same-host absolute' => ["{$base}/stones", "{$base}/ar/stones"],
            'already localized' => ['/en/contact', '/en/contact'],
            'external' => ['https://example.org/contact', 'https://example.org/contact'],
            'protocol-relative' => ['//example.org/x', '//example.org/x'],
            'tel' => ['tel:+966500000000', 'tel:+966500000000'],
            'anchor' => ['#faq', '#faq'],
            'relative without slash' => ['contact', 'contact'],
            'system route' => ['/sitemap.xml', '/sitemap.xml'],
            'asset' => ['/images/site/hero.webp', '/images/site/hero.webp'],
            'admin' => ['/admin', '/admin'],
            'empty' => ['', ''],
            'null' => [null, null],
        ];
    }

    #[DataProvider('links')]
    public function test_internal_links_get_the_locale_prefix(?string $href, ?string $expected): void
    {
        $base = rtrim(url('/'), '/');
        $resolve = fn (?string $value) => $value === null ? null : str_replace('{base}', $base, $value);

        $this->assertSame($resolve($expected), LocalizedUrl::link($resolve($href), 'ar'));
    }

    public function test_slide_buttons_link_to_the_visitors_language(): void
    {
        Slider::factory()->create(['button_text_en' => 'Get a quote', 'button_text_ar' => 'اطلب عرض سعر', 'button_link' => '/contact', 'is_active' => true]);

        $this->get(route('home', ['locale' => 'en']))->assertSee('href="'.url('/en/contact').'"', false);
        $this->get(route('home', ['locale' => 'ar']))->assertSee('href="'.url('/ar/contact').'"', false)
            ->assertDontSee('href="/contact"', false);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TypographyTest extends TestCase
{
    use RefreshDatabase;

    public function test_specimen_page_is_local_only(): void
    {
        $this->get('/dev/typography')->assertNotFound();

        $this->app['env'] = 'local';
        $this->get('/dev/typography')->assertOk()->assertSee('lang="ar"', false)->assertSee('lang="en"', false);

        $this->app['env'] = 'production';
        $this->get('/dev/typography')->assertNotFound();
    }

    public function test_arabic_never_gets_letter_spacing_or_faux_styles(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString("[class*='tracking-']:lang(ar)", $css);
        $this->assertStringContainsString('font-synthesis: none', $css);
        $this->assertStringContainsString('--leading-base: 1.85', $css, 'Arabic body line-height');

        foreach (glob(resource_path('views/**/*.blade.php')) ?: [] as $view) {
            $this->assertDoesNotMatchRegularExpression('/\brtl:tracking-|\buppercase\b/', file_get_contents($view), $view);
        }
    }

    public function test_fonts_above_the_fold_are_preloaded_per_language(): void
    {
        $this->useLocale('ar');
        $this->get(route('home'))->assertSee('rel="preload" as="font"', false);
    }
}

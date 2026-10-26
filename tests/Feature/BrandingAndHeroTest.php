<?php

namespace Tests\Feature;

use App\Filament\Pages\Settings;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\User;
use App\Support\SiteImages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BrandingAndHeroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Never touch the real storage/app/public during tests.
        Storage::fake('public');
    }

    private function actingAsAdmin(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));
    }

    public function test_defaults_are_used_until_branding_is_uploaded(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString(asset(Setting::DEFAULT_LOGO), $html);
        $this->assertStringContainsString(asset(Setting::DEFAULT_LOGO_LIGHT), $html);
        $this->assertStringContainsString('<meta property="og:image" content="'.asset(Setting::DEFAULT_OG_IMAGE).'">', $html);
    }

    public function test_admin_can_upload_logo_and_share_image(): void
    {
        $this->actingAsAdmin();

        Livewire::test(Settings::class)
            ->set('data.logo', [UploadedFile::fake()->image('brand-logo.png', 560, 112)])
            ->set('data.og_image', [UploadedFile::fake()->image('share.jpg', 1200, 630)])
            ->call('save')
            ->assertHasNoErrors();

        $setting = Setting::current();
        $this->assertNotNull($setting->getFirstMedia('logo'));
        $this->assertTrue($setting->getFirstMedia('og_image')->hasGeneratedConversion('og'));

        auth()->logout();
        $html = $this->get(route('home'))->getContent();
        $this->assertStringContainsString($setting->logoUrl(), $html);
        $this->assertStringContainsString('<meta property="og:image" content="'.$setting->ogImageUrl().'">', $html);
        $this->assertStringContainsString('-og.jpg', $setting->ogImageUrl());
        $this->assertStringNotContainsString(asset(Setting::DEFAULT_OG_IMAGE), $html);
    }

    public function test_svg_uploads_are_rejected(): void
    {
        $this->actingAsAdmin();

        Livewire::test(Settings::class)
            ->set('data.logo', [UploadedFile::fake()->create('evil.svg', 5, 'image/svg+xml')])
            ->call('save')
            ->assertHasErrors();

        $this->assertNull(Setting::current()->getFirstMedia('logo'));
    }

    public function test_extra_social_links_render_in_footer_and_json_ld(): void
    {
        Setting::editable()->update([
            'linkedin_url' => 'https://www.linkedin.com/company/example',
            'x_url' => 'https://x.com/example',
            'youtube_url' => 'https://youtube.com/@example',
        ]);

        $html = $this->get(route('home'))->assertOk()->getContent();

        foreach (['LinkedIn', 'X', 'YouTube'] as $label) {
            $this->assertStringContainsString('aria-label="'.$label.'"', $html);
        }
        $this->assertStringContainsString('"sameAs":["https://x.com/example","https://www.linkedin.com/company/example","https://youtube.com/@example"]', $html);
    }

    public function test_admin_can_save_extra_social_links(): void
    {
        $this->actingAsAdmin();

        Livewire::test(Settings::class)
            ->set('data.linkedin_url', 'https://www.linkedin.com/company/example')
            ->set('data.youtube_url', 'not a url')
            ->call('save')
            ->assertHasErrors(['data.youtube_url']);

        Livewire::test(Settings::class)
            ->set('data.linkedin_url', 'https://www.linkedin.com/company/example')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('https://www.linkedin.com/company/example', Setting::current()->linkedin_url);
    }

    public function test_hero_uses_the_curated_photo_when_no_slide_has_an_image(): void
    {
        Slider::create(['title_en' => 'Slide', 'title_ar' => 'شريحة', 'is_active' => true]);

        $hero = SiteImages::url('hero');
        $this->assertNotNull($hero, 'public/images/site/hero.webp is committed');

        $this->get(route('home'))
            ->assertSee('<link rel="preload" as="image" href="'.$hero.'"', false)
            ->assertDontSee('videos/hero-background.mp4', false);
    }

    public function test_hero_falls_back_to_video_and_poster_without_a_curated_photo(): void
    {
        config(['site-images.slots' => []]); // no slot => SiteImages::exists() is false
        Slider::create(['title_en' => 'Slide', 'title_ar' => 'شريحة', 'is_active' => true]);

        $this->get(route('home'))
            ->assertSee('videos/hero-background.mp4', false)
            ->assertSee('<link rel="preload" as="image" href="'.asset('images/hero-poster.webp').'"', false);
    }

    public function test_hero_renders_slide_images_instead_of_the_video(): void
    {
        $first = Slider::create(['title_en' => 'First', 'title_ar' => 'الأولى', 'is_active' => true, 'order' => 1]);
        $first->addMedia(UploadedFile::fake()->image('first-slide.jpg', 1920, 1080))->toMediaCollection('image');
        Slider::create(['title_en' => 'Second', 'title_ar' => 'الثانية', 'is_active' => true, 'order' => 2]);

        $heroUrl = $first->fresh()->heroImageUrl();
        $this->assertStringContainsString('first-slide-hero.webp', $heroUrl);

        $this->get(route('home'))
            ->assertSee($heroUrl, false)
            ->assertSee('<link rel="preload" as="image" href="'.$heroUrl.'"', false)
            ->assertDontSee('videos/hero-background.mp4', false);
    }

    public function test_slide_without_hero_conversion_falls_back_to_original(): void
    {
        $slider = Slider::create(['title_en' => 'Old', 'title_ar' => 'قديم', 'is_active' => true]);
        $media = $slider->addMedia(UploadedFile::fake()->image('legacy.jpg', 800, 600))->toMediaCollection('image');
        $media->markAsConversionNotGenerated('hero')->save();

        $this->assertSame($media->getUrl(), $slider->fresh()->heroImageUrl());
    }
}

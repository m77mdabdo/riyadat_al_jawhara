<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ResponsiveImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploads_get_webp_renditions_and_pages_use_srcset(): void
    {
        $project = Project::create(['title_en' => 'Villa', 'title_ar' => 'فيلا', 'is_active' => true]);
        $media = $project->addMedia(UploadedFile::fake()->image('villa.jpg', 2000, 1200))->toMediaCollection('cover');

        $this->assertTrue($media->hasGeneratedConversion('card'));
        $this->assertTrue($media->hasGeneratedConversion('large'));
        $this->assertStringEndsWith('.webp', $media->getUrl('card'));
        // Existing JPEG conversions are unchanged (share images, legacy media).
        $this->assertStringEndsWith('.jpg', $media->getUrl('medium'));

        $html = $this->get(route('projects.index'))->getContent();
        $this->assertStringContainsString('srcset="'.$media->getUrl('card').' 800w, '.$media->getUrl('large').' 1600w"', $html);
        $this->assertStringContainsString('sizes="(min-width: 1024px) 33vw', $html);

        $this->get(route('projects.show', $project))->assertSee('sizes="(min-width: 1024px) 960px, 100vw"', false);
    }

    public function test_legacy_media_without_webp_falls_back_without_srcset(): void
    {
        $project = Project::create(['title_en' => 'Old', 'title_ar' => 'قديم', 'is_active' => true]);
        $media = $project->addMedia(UploadedFile::fake()->image('old.jpg', 1200, 800))->toMediaCollection('cover');
        $media->markAsConversionNotGenerated('card')->markAsConversionNotGenerated('large')->save();

        $html = $this->get(route('projects.index'))->getContent();

        $this->assertStringContainsString($media->getUrl('medium'), $html);
        // (Static site images such as the page-header texture have their own srcset.)
        $this->assertDoesNotMatchRegularExpression('/srcset="[^"]*old-/', $html);
    }

    public function test_small_avatars_do_not_get_a_large_srcset(): void
    {
        $testimonial = Testimonial::create(['client_name' => 'Client', 'comment_en' => 'Great', 'comment_ar' => 'ممتاز', 'rating' => 5, 'is_active' => true]);
        $testimonial->addMedia(UploadedFile::fake()->image('face.jpg', 600, 600))->toMediaCollection('client_photo');

        $html = $this->get(route('home'))->getContent();

        $this->assertStringContainsString($testimonial->getFirstMedia('client_photo')->getUrl('thumb'), $html);
        $this->assertDoesNotMatchRegularExpression('/srcset="[^"]*face-/', $html);
    }

    public function test_lightbox_prefers_webp_renditions(): void
    {
        $category = StoneCategory::create(['name_en' => 'Marble', 'name_ar' => 'رخام']);
        $stone = StoneType::create(['stone_category_id' => $category->id, 'name_en' => 'Carrara', 'name_ar' => 'كرارا', 'is_active' => true]);
        $media = $stone->addMedia(UploadedFile::fake()->image('slab.jpg', 1800, 1200))->toMediaCollection('gallery');

        $html = $this->get(route('stones.show', $stone))->getContent();

        // Viewer items are passed to Alpine via @js, so compare the file names.
        $this->assertStringContainsString('slab-large.webp', $html);
        $this->assertStringContainsString($media->getUrl('card'), $html);
    }
}

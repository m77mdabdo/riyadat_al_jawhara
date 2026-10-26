<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\StoneCategory;
use App\Models\StoneType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    private function withGallery($model, int $count = 1, int $w = 800, int $h = 600): void
    {
        for ($i = 0; $i < $count; $i++) {
            $model->addMedia(UploadedFile::fake()->image("g{$i}.jpg", $w, $h))->toMediaCollection('gallery');
        }
    }

    public function test_gallery_combines_active_project_and_stone_galleries(): void
    {
        $marble = StoneCategory::factory()->create(['slug' => 'marble']);
        $stone = StoneType::factory()->create(['name_en' => 'Carrara', 'stone_category_id' => $marble->id]);
        $project = Project::factory()->create(['title_en' => 'Villa Lobby', 'stone_type_id' => $stone->id]);
        $hidden = Project::factory()->inactive()->create(['title_en' => 'Hidden Project']);
        $this->withGallery($stone);
        $this->withGallery($project);
        $this->withGallery($hidden);

        $response = $this->get(route('gallery'))->assertOk();

        $this->assertCount(2, $response->viewData('media'));
        $response->assertSee('Carrara')->assertSee('Villa Lobby')->assertDontSee('Hidden Project');
    }

    public function test_filters_by_type_and_stone_category(): void
    {
        $marble = StoneCategory::factory()->create(['slug' => 'marble']);
        $granite = StoneCategory::factory()->create(['slug' => 'granite']);
        $carrara = StoneType::factory()->create(['stone_category_id' => $marble->id]);
        $black = StoneType::factory()->create(['stone_category_id' => $granite->id]);
        $marbleProject = Project::factory()->create(['stone_type_id' => $carrara->id]);
        $this->withGallery($carrara);
        $this->withGallery($black);
        $this->withGallery($marbleProject);

        $count = fn (array $q) => $this->get(route('gallery', $q))->viewData('media')->total();

        $this->assertSame(3, $count([]));
        $this->assertSame(2, $count(['type' => 'stones']));
        $this->assertSame(1, $count(['type' => 'projects']));
        $this->assertSame(2, $count(['stone_category' => 'marble']));
        $this->assertSame(1, $count(['type' => 'stones', 'stone_category' => 'granite']));
        $this->assertSame(3, $count(['type' => 'bogus']));
    }

    public function test_masonry_tiles_have_real_dimensions_and_open_the_shared_lightbox(): void
    {
        $stone = StoneType::factory()->create();
        $this->withGallery($stone, 1, 600, 900);

        $html = $this->get(route('gallery'))->getContent();

        $this->assertStringContainsString('columns-2 sm:columns-3 lg:columns-4', $html);
        $this->assertMatchesRegularExpression('/<img src="[^"]+" alt="" width="\d+" height="\d+"/', $html);
        $this->assertStringContainsString('role="dialog"', $html);
        preg_match('/width="(\d+)" height="(\d+)"/', substr($html, strpos($html, 'columns-2')), $m);
        $this->assertGreaterThan((int) $m[1], (int) $m[2], 'portrait tile keeps its aspect');
    }

    public function test_paginates_24_per_page(): void
    {
        $stone = StoneType::factory()->create();
        $this->withGallery($stone, 25, 40, 30);

        $this->assertCount(24, $this->get(route('gallery'))->viewData('media'));
        $this->assertCount(1, $this->get(route('gallery', ['page' => 2]))->viewData('media'));
    }

    public function test_empty_state_nav_and_sitemap(): void
    {
        $this->get(route('gallery'))->assertOk()->assertSee(__('site.gallery.empty'));
        $this->get(route('home'))->assertSee('href="'.route('gallery').'"', false);
        $this->get('/sitemap.xml')->assertSee(url('/ar/gallery'), false)->assertSee(url('/en/gallery'), false);

        $this->useLocale('ar');
        $this->get(route('gallery'))->assertOk()->assertSee('معرض الصور');
    }
}

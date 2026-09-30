<?php

namespace Tests\Feature;

use App\Filament\Resources\StoneCategoryResource\Pages\CreateStoneCategory;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Models\User;
use App\Support\SiteImages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoneCategoryLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_filtered_catalogue_shows_the_category_landing_section(): void
    {
        $marble = StoneCategory::factory()->create(['slug' => 'marble', 'name_en' => 'Marble', 'description_en' => 'Classic metamorphic stone.', 'description_ar' => 'حجر كلاسيكي.']);
        $marble->addMedia(UploadedFile::fake()->image('marble.jpg', 1600, 1200))->toMediaCollection('cover');
        StoneType::factory()->create(['stone_category_id' => $marble->id]);

        $this->get(route('stones.index', ['stone_category' => 'marble']))
            ->assertOk()
            ->assertSee('Classic metamorphic stone.')
            ->assertSee($marble->fresh()->getFirstMedia('cover')->getUrl('card'), false);

        $this->useLocale('ar');
        $this->get(route('stones.index', ['stone_category' => 'marble']))->assertSee('حجر كلاسيكي.');

        $this->get(route('stones.index'))->assertDontSee('حجر كلاسيكي.');
    }

    public function test_description_falls_back_to_the_other_language(): void
    {
        $cat = StoneCategory::factory()->create(['slug' => 'granite', 'description_en' => 'Hard igneous stone.', 'description_ar' => null]);

        $this->useLocale('ar');
        $this->get(route('stones.index', ['stone_category' => 'granite']))->assertSee('Hard igneous stone.');
    }

    public function test_home_tile_uses_the_category_image_or_the_texture(): void
    {
        $withImage = StoneCategory::factory()->create(['slug' => 'marble']);
        $withImage->addMedia(UploadedFile::fake()->image('tile.jpg', 1200, 900))->toMediaCollection('cover');
        StoneCategory::factory()->create(['slug' => 'granite']);

        $html = $this->get(route('home'))->getContent();

        $this->assertStringContainsString($withImage->fresh()->getFirstMedia('cover')->getUrl('card'), $html);
        $this->assertStringContainsString(SiteImages::url('texture-granite'), $html);
    }

    public function test_admin_can_set_description_and_image(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));

        Livewire::test(CreateStoneCategory::class)
            ->fillForm([
                'name_en' => 'Onyx', 'name_ar' => 'أونكس', 'slug' => 'onyx',
                'description_en' => 'Translucent stone.', 'description_ar' => 'حجر شفاف.',
                'cover' => [UploadedFile::fake()->image('onyx.webp', 800, 600)],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $onyx = StoneCategory::where('slug', 'onyx')->firstOrFail();
        $this->assertSame('Translucent stone.', $onyx->description_en);
        $this->assertNotNull($onyx->getFirstMedia('cover'));
    }
}

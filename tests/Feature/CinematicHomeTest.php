<?php

namespace Tests\Feature;

use App\Filament\Pages\Homepage;
use App\Models\Setting;
use App\Models\StoneType;
use App\Models\User;
use App\Support\HomeSections;
use App\Support\SiteImages;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CinematicHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_the_journey_chapters_with_their_photos(): void
    {
        $html = $this->get(route('home', ['locale' => 'en']))->assertOk()->getContent();

        $this->assertStringContainsString(__('site.home.journey_title', [], 'en'), $html);
        foreach (__('site.home.journey_items', [], 'en') as $key => $item) {
            $this->assertStringContainsString(e($item['title']), $html);
            if (SiteImages::exists('cinema-'.$key)) {
                $this->assertStringContainsString(SiteImages::url('cinema-'.$key), $html);
            }
        }
        // Scroll motion is opt-in per element and the hero takes part in it.
        $this->assertSame(1 + count(__('site.home.journey_items', [], 'en')), substr_count($html, 'x-cinema'));
        $this->assertStringContainsString('hero-cinema', $html);
        // Reveal and cinema directives only run inside an Alpine scope.
        $this->assertStringContainsString('<main id="main-content" class="flex-1" x-data>', $html);
    }

    public function test_the_arabic_home_page_has_arabic_chapters(): void
    {
        $this->get(route('home', ['locale' => 'ar']))
            ->assertOk()
            ->assertSee(__('site.home.journey_title', [], 'ar'))
            ->assertSee(__('site.home.journey_items.quarry.title', [], 'ar'));
    }

    public function test_a_section_added_later_keeps_its_built_in_place_in_a_saved_order(): void
    {
        $saved = array_values(array_diff(HomeSections::SECTIONS, ['journey']));
        Setting::editable()->update(['home_sections' => ['order' => array_reverse($saved)]]);
        Setting::flushCache();

        $order = HomeSections::order();
        // 'why' comes before 'journey' in the built-in order, so journey follows it.
        $this->assertSame(array_search('why', $order, true) + 1, array_search('journey', $order, true));
        $this->assertCount(count(HomeSections::SECTIONS), $order);
    }

    public function test_admin_can_edit_and_hide_the_journey(): void
    {
        Storage::fake('public');
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->seed(DemoSeeder::class);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));

        $component = Livewire::test(Homepage::class);
        $items = $component->get('data.home_sections.sections.journey.items');
        $this->assertCount(3, $items);
        $first = array_key_first($items);
        $this->assertSame('cinema-quarry', $items[$first]['slot']);
        $items[$first]['title_en'] = 'Selected in person';
        $items[$first]['title_ar'] = 'نختاره بأنفسنا';

        $component->set('data.home_sections.sections.journey.items', $items)->call('save')->assertHasNoErrors();
        $this->get(route('home', ['locale' => 'en']))->assertSee('Selected in person');
        $this->get(route('home', ['locale' => 'ar']))->assertSee('نختاره بأنفسنا');

        $layout = collect($component->get('data.layout'))
            ->map(fn (array $row) => $row['section'] === 'journey' ? ['enabled' => false] + $row : $row)->all();
        $component->set('data.layout', $layout)->call('save')->assertHasNoErrors();
        $this->get(route('home', ['locale' => 'en']))->assertDontSee('Selected in person');
    }

    public function test_attach_stones_sets_credited_covers_and_keeps_a_backup(): void
    {
        Storage::fake('public');
        $stone = StoneType::factory()->create(['slug' => 'carrara-marble', 'name_en' => 'Carrara Marble', 'name_ar' => 'رخام كرارا']);
        $old = $stone->addMedia(SiteImages::path('texture-marble'))->preservingOriginal()->toMediaCollection('cover');

        $credits = storage_path('framework/testing/credits.md');
        File::put($credits, "# Image credits\n");
        $backups = storage_path('framework/testing/media-backup');
        config(['site-images.credits_path' => $credits, 'site-images.backup_path' => $backups]);

        $this->artisan('site-images:attach-stones')->assertSuccessful();

        $cover = $stone->fresh()->getFirstMedia('cover');
        $this->assertNotSame($old->id, $cover->id);
        $this->assertSame(SiteImages::choices()['stone-carrara-marble']['id'], $cover->getCustomProperty('stock_id'));
        $this->assertNotEmpty($cover->getCustomProperty('photographer'));
        $this->assertSame('Carrara Marble', $cover->getCustomProperty('alt_en'));
        $this->assertFileExists("{$backups}/stone-carrara-marble-cover-{$old->id}-{$old->file_name}");
        $this->assertStringContainsString('Carrara Marble (`carrara-marble`)', File::get($credits));

        // Running it again changes nothing.
        $this->artisan('site-images:attach-stones')->expectsOutputToContain('already uses')->assertSuccessful();

        File::delete($credits);
        File::deleteDirectory($backups);
    }
}

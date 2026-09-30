<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceAndDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, string>
     */
    private function queriesFor(string $url): array
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $queries = array_column(DB::getQueryLog(), 'query');
        DB::disableQueryLog();

        return $queries;
    }

    public function test_get_requests_never_insert_a_settings_row(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('contact'))->assertOk();
        $this->get(route('about'))->assertOk();

        $this->assertSame(0, Setting::count());
    }

    public function test_settings_are_loaded_once_per_request_and_then_cached(): void
    {
        Setting::editable()->update(['phone' => '+966 11 111 1111']);

        $first = collect($this->queriesFor(route('contact')))->filter(fn ($q) => str_contains($q, '"settings"'));
        $second = collect($this->queriesFor(route('contact')))->filter(fn ($q) => str_contains($q, '"settings"'));

        $this->assertLessThanOrEqual(1, $first->count());
        $this->assertCount(0, $second, 'second request is served from cache');
    }

    public function test_saving_settings_invalidates_the_cache(): void
    {
        Setting::editable()->update(['phone' => '+966 11 111 1111']);
        $this->get(route('contact'))->assertSee('+966 11 111 1111');

        Setting::editable()->update(['phone' => '+966 22 222 2222']);
        $this->get(route('contact'))->assertSee('+966 22 222 2222')->assertDontSee('+966 11 111 1111');
    }

    public function test_empty_translation_falls_back_to_the_other_language(): void
    {
        $service = Service::create(['name_en' => 'Stone Supply', 'name_ar' => '', 'description_en' => 'English only text', 'is_active' => true]);
        Faq::create(['question_en' => '', 'question_ar' => 'سؤال عربي فقط', 'answer_en' => 'Answer', 'answer_ar' => 'جواب', 'is_active' => true]);

        $this->useLocale('ar');
        $this->get(route('services.show', $service))->assertSee('Stone Supply')->assertSee('English only text');

        $this->useLocale('en');
        $this->get(route('faq'))->assertSee('سؤال عربي فقط');

        app()->setLocale('ar');
        $this->assertSame('Stone Supply', $service->name);
        app()->setLocale('en');
        $this->assertSame('English only text', $service->description);
    }

    public function test_list_pages_load_media_without_n_plus_one(): void
    {
        $this->seed(DemoSeeder::class);

        // Give every project a cover so the list really renders images.
        Project::all()->each(fn (Project $p) => $p->addMedia(UploadedFile::fake()->image("p{$p->id}.jpg", 800, 600))->toMediaCollection('cover'));

        foreach ([route('home'), route('services.index'), route('stones.index'), route('projects.index'), route('blog.index'), route('about')] as $url) {
            $mediaQueries = collect($this->queriesFor($url))->filter(fn ($q) => str_contains($q, 'from "media"'))->count();

            // One eager-load query per model type on the page, never one per card.
            // Home shows 7 media-bearing types (slides, services, projects, stones,
            // stone categories, testimonials, settings logo); others at most 6.
            $this->assertLessThanOrEqual($url === route('home') ? 7 : 6, $mediaQueries, $url);
        }

        $projectsPage = collect($this->queriesFor(route('projects.index')))->filter(fn ($q) => str_contains($q, 'from "media"'));
        $this->assertCount(1, $projectsPage);
    }
}

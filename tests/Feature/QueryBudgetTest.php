<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use App\Models\TeamMember;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Query budgets per public page, measured with demo content and an image on
 * every card (settings served from cache after the first request). A page
 * going over budget usually means a missing eager load (N+1).
 */
class QueryBudgetTest extends TestCase
{
    use RefreshDatabase;

    private const BUDGETS = [
        'home' => 13,
        'services' => 4,
        'service' => 5,
        'stones' => 8,
        'stone' => 8,
        'projects' => 8,
        'project' => 7,
        'blog' => 8,
        'post' => 11,
        'faq' => 3,
        'about' => 4,
        'contact' => 4,
        'privacy' => 2,
        'sitemap' => 12,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);

        foreach ([Project::class, Service::class, StoneType::class, Post::class] as $model) {
            $model::all()->each(fn ($record) => $record->addMedia(UploadedFile::fake()->image('cover.jpg', 400, 300))->toMediaCollection('cover'));
        }
        TeamMember::all()->each(fn ($member) => $member->addMedia(UploadedFile::fake()->image('photo.jpg', 300, 300))->toMediaCollection('photo'));

        $this->get(route('home')); // warm the settings cache
    }

    private function queryCount(string $url): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($url)->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    public function test_pages_stay_within_their_query_budget(): void
    {
        $urls = [
            'home' => route('home'),
            'services' => route('services.index'),
            'service' => route('services.show', Service::first()),
            'stones' => route('stones.index'),
            'stone' => route('stones.show', StoneType::first()),
            'projects' => route('projects.index'),
            'project' => route('projects.show', Project::first()),
            'blog' => route('blog.index'),
            'post' => route('blog.show', Post::published()->first()),
            'faq' => route('faq'),
            'about' => route('about'),
            'contact' => route('contact'),
            'privacy' => route('legal.privacy'),
            'sitemap' => route('sitemap'),
        ];

        foreach ($urls as $page => $url) {
            $this->assertLessThanOrEqual(self::BUDGETS[$page], $this->queryCount($url), "{$page} ({$url})");
        }
    }

    public function test_list_query_count_does_not_grow_with_the_number_of_items(): void
    {
        $before = $this->queryCount(route('projects.index'));

        Project::factory()->count(6)->create()->each(
            fn ($p) => $p->addMedia(UploadedFile::fake()->image('c.jpg', 400, 300))->toMediaCollection('cover')
        );

        $this->assertSame($before, $this->queryCount(route('projects.index')));
    }
}

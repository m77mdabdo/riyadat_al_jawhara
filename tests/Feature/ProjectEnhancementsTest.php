<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProjectEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_similar_projects_prefer_same_stone_then_service_then_sector(): void
    {
        $stone = StoneType::factory()->create();
        $service = Service::factory()->create();
        $project = Project::factory()->create(['stone_type_id' => $stone->id, 'service_id' => $service->id, 'sector' => 'residential']);

        $sameStone = Project::factory()->create(['title_en' => 'Same Stone', 'sector' => 'industrial']);
        $sameStone->stoneTypes()->attach($stone);
        $sameService = Project::factory()->create(['title_en' => 'Same Service', 'service_id' => $service->id, 'sector' => 'industrial']);
        $sameSector = Project::factory()->create(['title_en' => 'Same Sector', 'sector' => 'residential']);
        Project::factory()->create(['title_en' => 'Unrelated', 'sector' => 'government']);
        Project::factory()->inactive()->create(['title_en' => 'Hidden', 'stone_type_id' => $stone->id]);

        $this->assertSame(['Same Stone', 'Same Service', 'Same Sector'], $project->similar()->pluck('title_en')->all());
        $this->assertSame(['Same Stone'], $project->similar(1)->pluck('title_en')->all());

        $this->get(route('projects.show', $project))
            ->assertSee(__('site.projects.similar'))
            ->assertSee('Same Stone')
            ->assertDontSee('Unrelated')
            ->assertDontSee('Hidden');
    }

    public function test_no_similar_block_without_matches(): void
    {
        $project = Project::factory()->create(['stone_type_id' => null, 'service_id' => null, 'sector' => null]);
        Project::factory()->create();

        $this->get(route('projects.show', $project))->assertDontSee(__('site.projects.similar'));
    }

    public function test_client_name_is_private_unless_explicitly_enabled(): void
    {
        $project = Project::factory()->create(['client_name' => 'Al-Faisal Holding']);

        $this->assertFalse($project->fresh()->show_client_name);
        $this->get(route('projects.show', $project))->assertDontSee('Al-Faisal Holding');

        $project->update(['show_client_name' => true]);
        $this->get(route('projects.show', $project))->assertSee('Al-Faisal Holding')->assertSee(__('site.projects.client'));
    }

    public function test_before_and_after_photos_are_not_duplicated_in_the_gallery(): void
    {
        $project = Project::factory()->create();
        foreach (['before', 'after', 'gallery'] as $collection) {
            $project->addMedia(UploadedFile::fake()->image("{$collection}.jpg", 400, 300))->toMediaCollection($collection);
        }

        $html = $this->get(route('projects.show', $project))->getContent();
        preg_match('/items: (JSON\.parse\(.*?\)|\[.*?\])/s', $html, $m);

        $this->assertStringContainsString('gallery', $m[1] ?? $html);
        $this->assertStringNotContainsString('before-', $m[1] ?? '');
        $this->assertStringNotContainsString('after-', $m[1] ?? '');
    }
}

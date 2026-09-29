<?php

namespace Tests\Feature;

use App\Filament\Resources\ProjectResource\Pages\CreateProject;
use App\Filament\Resources\ProjectResource\Pages\EditProject;
use App\Models\Project;
use App\Models\StoneCategory;
use App\Models\StoneType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectMetadataTest extends TestCase
{
    use RefreshDatabase;

    private StoneType $carrara;

    private StoneType $granite;

    private StoneType $hidden;

    protected function setUp(): void
    {
        parent::setUp();

        $category = StoneCategory::create(['name_en' => 'Stone', 'name_ar' => 'حجر']);
        $this->carrara = StoneType::create(['stone_category_id' => $category->id, 'name_en' => 'Carrara', 'name_ar' => 'كرارا', 'is_active' => true]);
        $this->granite = StoneType::create(['stone_category_id' => $category->id, 'name_en' => 'Black Granite', 'name_ar' => 'جرانيت أسود', 'is_active' => true]);
        $this->hidden = StoneType::create(['stone_category_id' => $category->id, 'name_en' => 'Retired Stone', 'name_ar' => 'قديم', 'is_active' => false]);
    }

    private function project(string $title, array $attributes = [], array $extraStones = []): Project
    {
        $project = Project::create($attributes + ['title_en' => $title, 'title_ar' => $title.' ع', 'is_active' => true]);
        $project->stoneTypes()->attach(collect($extraStones)->pluck('id'));

        return $project;
    }

    public function test_detail_page_shows_metadata_and_all_active_materials(): void
    {
        $project = $this->project('Villa', [
            'stone_type_id' => $this->carrara->id,
            'sector' => 'residential',
            'area_m2' => 1250.5,
            'city_en' => 'Riyadh',
            'city_ar' => 'الرياض',
        ], [$this->granite, $this->carrara, $this->hidden]);

        $response = $this->get(route('projects.show', $project))->assertOk();

        $response->assertSee('Riyadh')
            ->assertSee(__('site.projects.sectors.residential'))
            ->assertSee('1,250.5 m²')
            ->assertSee(route('stones.show', $this->carrara), false)
            ->assertSee(route('stones.show', $this->granite), false)
            ->assertDontSee('Retired Stone');

        $this->assertSame(['Carrara', 'Black Granite'], $project->fresh()->allStoneTypes()->pluck('name_en')->all());
    }

    public function test_arabic_page_uses_arabic_city_and_sector(): void
    {
        $project = $this->project('Villa', ['sector' => 'commercial', 'city_en' => 'Riyadh', 'city_ar' => 'الرياض']);

        $this->get('/lang/ar');
        $this->get(route('projects.show', $project))->assertSee('الرياض')->assertSee('تجاري');
    }

    public function test_unknown_sector_value_is_not_rendered(): void
    {
        $project = $this->project('Villa', ['sector' => 'space-station']);

        $this->get(route('projects.show', $project))->assertOk()->assertDontSee('site.projects.sectors');
    }

    public function test_stone_filter_matches_primary_and_additional_stones(): void
    {
        $primary = $this->project('Primary Carrara', ['stone_type_id' => $this->carrara->id]);
        $additional = $this->project('Additional Carrara', ['stone_type_id' => $this->granite->id], [$this->carrara]);
        $other = $this->project('Granite Only', ['stone_type_id' => $this->granite->id]);

        $titles = fn (array $q) => $this->get(route('projects.index', $q))->viewData('projects')->pluck('title_en')->all();

        $this->assertEqualsCanonicalizing(['Primary Carrara', 'Additional Carrara'], $titles(['stone_type' => $this->carrara->slug]));
        $this->assertSame([], $titles(['stone_type' => 'does-not-exist']));

        $this->get(route('stones.show', $this->carrara))
            ->assertSee('Primary Carrara')
            ->assertSee('Additional Carrara')
            ->assertDontSee('Granite Only');
    }

    public function test_sector_filter(): void
    {
        $this->project('Home', ['sector' => 'residential']);
        $this->project('Mall', ['sector' => 'commercial']);

        $response = $this->get(route('projects.index', ['sector' => 'commercial']))->assertOk();
        $this->assertSame(['Mall'], $response->viewData('projects')->pluck('title_en')->all());
        $response->assertSee('<option value="residential"', false);

        $this->assertCount(2, $this->get(route('projects.index', ['sector' => 'bogus']))->viewData('projects'));
    }

    public function test_admin_can_save_metadata_and_additional_stones(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));

        Livewire::test(CreateProject::class)
            ->fillForm([
                'title_en' => 'Tower Lobby', 'title_ar' => 'ردهة البرج', 'slug' => 'tower-lobby',
                'sector' => 'commercial', 'area_m2' => '850', 'city_en' => 'Jeddah', 'city_ar' => 'جدة',
                'stone_type_id' => $this->carrara->id,
                'stoneTypes' => [$this->granite->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $project = Project::where('slug', 'tower-lobby')->firstOrFail();
        $this->assertSame('commercial', $project->sector);
        $this->assertSame('850.00', $project->area_m2);
        $this->assertSame([$this->granite->id], $project->stoneTypes->pluck('id')->all());
        $this->assertSame($this->carrara->id, $project->stone_type_id);

        Livewire::test(EditProject::class, ['record' => $project->getRouteKey()])
            ->fillForm(['sector' => 'nope', 'area_m2' => '-5'])
            ->call('save')
            ->assertHasFormErrors(['sector', 'area_m2']);
    }
}

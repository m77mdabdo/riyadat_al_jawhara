<?php

namespace Tests\Feature;

use App\Filament\Resources\ProjectResource\Pages\EditProject;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoneType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeoOverridesTest extends TestCase
{
    use RefreshDatabase;

    public static function entities(): array
    {
        return [
            'service' => [Service::class, 'services.show', 'name_en'],
            'stone' => [StoneType::class, 'stones.show', 'name_en'],
            'project' => [Project::class, 'projects.show', 'title_en'],
            'post' => [Post::class, 'blog.show', 'title_en'],
        ];
    }

    #[DataProvider('entities')]
    public function test_overrides_replace_title_and_description_when_set(string $class, string $route, string $titleField): void
    {
        $record = $class::factory()->create([$titleField => 'Plain Title']);

        $this->get(route($route, $record))->assertSee('<title>Plain Title | ', false);

        $record->update(['meta_title_en' => 'Custom SEO Title', 'meta_description_en' => 'Custom SEO description.', 'meta_title_ar' => 'عنوان مخصص']);

        $this->get(route($route, $record))
            ->assertSee('<title>Custom SEO Title | ', false)
            ->assertSee('<meta name="description" content="Custom SEO description.">', false)
            ->assertSee('<meta property="og:title" content="Custom SEO Title">', false);

        $this->useLocale('ar');
        $this->get(route($route, $record))->assertSee('<title>عنوان مخصص | ', false)
            // No Arabic description override: falls back to the English override.
            ->assertSee('Custom SEO description.', false);
    }

    public function test_admin_can_edit_seo_fields_with_length_limits(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->actingAs(tap(User::factory()->create())->assignRole('admin'));
        $project = Project::factory()->create();

        Livewire::test(EditProject::class, ['record' => $project->getRouteKey()])
            ->fillForm(['meta_title_en' => str_repeat('x', 80)])
            ->call('save')
            ->assertHasFormErrors(['meta_title_en']);

        Livewire::test(EditProject::class, ['record' => $project->getRouteKey()])
            ->fillForm(['meta_title_en' => 'Villa lobby in Carrara marble', 'meta_description_ar' => 'وصف قصير'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Villa lobby in Carrara marble', $project->fresh()->meta_title_en);
    }
}

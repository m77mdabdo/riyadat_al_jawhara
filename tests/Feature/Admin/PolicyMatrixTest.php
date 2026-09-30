<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The admin/editor permission matrix, stated in one place.
 */
class PolicyMatrixTest extends TestCase
{
    use RefreshDatabase;

    private function user(?UserRole $role): User
    {
        foreach (UserRole::values() as $name) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        $user = User::factory()->create();

        return $role ? tap($user)->assignRole($role->value) : $user;
    }

    public function test_editors_manage_content_but_never_delete(): void
    {
        $editor = $this->user(UserRole::Editor);
        $project = Project::factory()->create();

        foreach (['viewAny', 'create'] as $ability) {
            $this->assertTrue($editor->can($ability, Project::class), $ability);
        }
        $this->assertTrue($editor->can('view', $project));
        $this->assertTrue($editor->can('update', $project));
        $this->assertFalse($editor->can('delete', $project));
        $this->assertFalse($editor->can('deleteAny', Project::class));
    }

    public function test_admin_only_areas(): void
    {
        $editor = $this->user(UserRole::Editor);
        $admin = $this->user(UserRole::Admin);

        foreach ([['viewAny', User::class], ['update', Setting::class], ['viewAny', Activity::class], ['export', Lead::class]] as [$ability, $class]) {
            $this->assertFalse($editor->can($ability, $class), "editor {$ability} {$class}");
            $this->assertTrue($admin->can($ability, $class), "admin {$ability} {$class}");
        }
    }

    public function test_notes_belong_to_their_author(): void
    {
        $author = $this->user(UserRole::Editor);
        $other = $this->user(UserRole::Editor);
        $note = LeadNote::factory()->create(['user_id' => $author->id]);

        $this->assertTrue($author->can('update', $note));
        $this->assertFalse($other->can('delete', $note));
        $this->assertTrue($this->user(UserRole::Admin)->can('delete', $note));
    }

    public function test_users_without_a_role_get_nothing(): void
    {
        $nobody = $this->user(null);

        $this->assertFalse($nobody->can('viewAny', Project::class));
        $this->assertFalse($nobody->can('create', Project::class));
    }
}

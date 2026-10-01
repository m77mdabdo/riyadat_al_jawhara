<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Resources\ActivityResource;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\RoleResource\Pages\CreateRole;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use App\Support\Permissions;
use Filament\Pages\Auth\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Roles & permissions (after app:import-site-content), account switches and
 * the activity log links.
 */
class RolesAndUsersTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'correct-horse-12345';

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('app:import-site-content');
    }

    private function userWithRole(string $role, array $attributes = []): User
    {
        return tap(User::factory()->create(['password' => Hash::make(self::PASSWORD)] + $attributes))->assignRole($role);
    }

    public function test_the_editor_matrix_is_unchanged_with_permissions(): void
    {
        $editor = $this->userWithRole('editor');
        $admin = $this->userWithRole('admin');
        $project = Project::factory()->create();

        $this->assertTrue($editor->usesPermissions());
        foreach (['viewAny', 'create'] as $ability) {
            $this->assertTrue($editor->can($ability, Project::class), $ability);
        }
        $this->assertTrue($editor->can('update', $project));
        $this->assertFalse($editor->can('delete', $project));
        $this->assertFalse($editor->can('restore', $project));
        $this->assertFalse($editor->can('forceDelete', $project));

        foreach ([['viewAny', User::class], ['update', Setting::class], ['viewAny', Activity::class], ['export', Lead::class], ['viewAny', Role::class]] as [$ability, $class]) {
            $this->assertFalse($editor->can($ability, $class), "editor {$ability} {$class}");
            $this->assertTrue($admin->can($ability, $class), "admin {$ability} {$class}");
        }
    }

    public function test_a_custom_role_only_reaches_its_areas(): void
    {
        $role = Role::create(['name' => 'Blog writer', 'guard_name' => 'web']);
        $role->givePermissionTo(['posts.view', 'posts.create', 'posts.update', 'tags.view']);
        $writer = $this->userWithRole('Blog writer');
        $post = Post::factory()->create();
        $this->actingAs($writer);

        $this->get('/admin')->assertOk();
        $this->get('/admin/posts')->assertOk();
        $this->get("/admin/posts/{$post->id}/edit")->assertOk();
        $this->get('/admin/tags')->assertOk();
        $this->get('/admin/tags/create')->assertForbidden();
        foreach (['/admin/projects', '/admin/leads', '/admin/menus', '/admin/homepage', '/admin/settings', '/admin/users', '/admin/roles'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->assertFalse($writer->can('delete', $post));

        // Delete granted later: allowed, still no permanent deletion.
        $role->givePermissionTo('posts.delete');
        $this->assertTrue($writer->fresh()->can('delete', $post));
        $this->assertFalse($writer->fresh()->can('forceDelete', $post));
    }

    public function test_admins_manage_roles_from_the_admin(): void
    {
        $this->actingAs($this->userWithRole('admin'));

        Livewire::test(CreateRole::class)
            ->fillForm(['name' => 'Sales', 'abilities' => ['leads' => ['view', 'update']], 'special' => ['leads.export']])
            ->call('create')
            ->assertHasNoFormErrors();

        $sales = Role::findByName('Sales');
        $this->assertEqualsCanonicalizing(['leads.view', 'leads.update', 'leads.export'], $sales->permissions->pluck('name')->all());

        $editor = Role::findByName('editor');
        Livewire::test(EditRole::class, ['record' => $editor->getRouteKey()])
            ->assertFormSet(['abilities.posts' => ['view', 'create', 'update']])
            ->fillForm(['abilities.posts' => ['view', 'create', 'update', 'delete']])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertTrue($editor->fresh()->hasPermissionTo('posts.delete'));
        $this->assertCount(count(Permissions::editorDefaults()) + 1, $editor->fresh()->permissions);

        // Built-in roles stay; a role in use stays.
        $this->assertFalse(RoleResource::canDelete($editor));
        $this->assertFalse(RoleResource::canEdit(Role::findByName('admin')));
        $this->assertTrue(RoleResource::canDelete($sales));
        $this->userWithRole('Sales');
        $this->assertFalse(RoleResource::canDelete($sales->fresh()));

        $this->actingAs($this->userWithRole('editor'));
        $this->get('/admin/roles')->assertForbidden();
    }

    public function test_inactive_accounts_cannot_sign_in_and_are_locked_out(): void
    {
        $user = $this->userWithRole('editor', ['email' => 'editor@example.test', 'is_active' => false]);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'editor@example.test', 'password' => self::PASSWORD])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);
        $this->assertGuest();

        $user->update(['is_active' => true]);
        Livewire::test(Login::class)
            ->fillForm(['email' => 'editor@example.test', 'password' => self::PASSWORD])
            ->call('authenticate')
            ->assertHasNoFormErrors();
        $this->assertNotNull($user->fresh()->last_login_at);

        // Deactivated while signed in: locked out on the next request.
        $user->update(['is_active' => false]);
        $this->actingAs($user->fresh())->get('/admin')->assertForbidden();
    }

    public function test_admins_toggle_accounts_but_never_their_own(): void
    {
        $admin = $this->userWithRole('admin');
        $editor = $this->userWithRole('editor');
        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->callTableAction('toggleActive', $editor)
            ->assertTableActionHidden('toggleActive', $admin)
            ->callTableAction('requirePassword', $editor);

        $this->assertFalse($editor->fresh()->is_active);
        $this->assertTrue($editor->fresh()->password_change_required);
    }

    public function test_a_required_password_change_comes_first(): void
    {
        $user = $this->userWithRole('editor', ['password_change_required' => true]);
        $this->actingAs($user);

        $this->get('/admin/posts')->assertRedirect('/admin/profile');
        $this->get('/admin/profile')->assertOk();

        Livewire::test(EditProfile::class)
            ->fillForm(['password' => 'a-brand-new-pass-42', 'passwordConfirmation' => 'a-brand-new-pass-42', 'currentPassword' => self::PASSWORD])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($user->fresh()->password_change_required);
        // New session (the old one holds the previous password hash).
        $this->flushSession();
        $this->actingAs($user->fresh())->get('/admin/posts')->assertOk();
    }

    public function test_activity_log_filters_and_links_to_the_record(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $project = Project::factory()->create();
        $activity = Activity::query()->where('subject_type', Project::class)->latest('id')->first();

        $this->assertSame(route('filament.admin.resources.projects.edit', $project), ActivityResource::subjectUrl($activity));
        $this->get("/admin/activities/{$activity->id}")->assertOk()->assertSee(route('filament.admin.resources.projects.edit', $project), false);
        $this->get('/admin/activities?'.http_build_query(['tableFilters' => ['created_at' => ['from' => now()->addDay()->toDateString()]]]))
            ->assertOk()->assertDontSee('Project #'.$project->id);

        $project->forceDelete();
        $this->assertNull(ActivityResource::subjectUrl($activity->fresh()));
    }
}

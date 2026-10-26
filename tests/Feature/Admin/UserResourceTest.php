<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $editorRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->editorRole = Role::create(['name' => 'editor', 'guard_name' => 'web']);
    }

    private function admin(): User
    {
        return tap(User::factory()->create())->assignRole('admin');
    }

    public function test_admin_can_create_a_user_with_a_role(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'New Editor',
                'email' => 'new.editor@example.test',
                'password' => 'a-strong-password-123',
                'roles' => [$this->editorRole->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'new.editor@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('editor'));
        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_edit_form_shows_the_current_role_and_can_change_it(): void
    {
        $this->actingAs($this->admin());
        $user = tap(User::factory()->create())->assignRole('editor');

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->assertFormSet(['roles' => [(string) $this->editorRole->id]])
            ->fillForm(['roles' => [$this->adminRole->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('editor'));
    }

    public function test_editing_without_a_new_password_keeps_the_old_one(): void
    {
        $this->actingAs($this->admin());
        $user = tap(User::factory()->create())->assignRole('editor');
        $hash = $user->password;

        Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['name' => 'Renamed'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($hash, $user->fresh()->password);
        $this->assertSame('Renamed', $user->fresh()->name);
    }

    public function test_editor_cannot_access_users_module(): void
    {
        $editor = tap(User::factory()->create())->assignRole('editor');

        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
        $this->actingAs($editor)->get('/admin/users/create')->assertForbidden();
    }
}

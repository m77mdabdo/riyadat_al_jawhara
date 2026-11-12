<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use App\Notifications\StaffAccessNotification;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * A team member with users.manage (but not the admin role) manages the other
 * accounts, never an administrator's, and cannot make anyone an administrator.
 */
class AdminAccountProtectionTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('app:import-site-content');

        Role::create(['name' => 'User manager', 'guard_name' => 'web'])->givePermissionTo('users.manage');
        $this->manager = tap(User::factory()->create())->assignRole('User manager');
        $this->admin = tap(User::factory()->create())->assignRole('admin');
    }

    private function editor(): User
    {
        return tap(User::factory()->create())->assignRole('editor');
    }

    public function test_a_manager_cannot_update_or_delete_an_administrator(): void
    {
        $editor = $this->editor();

        $this->assertFalse($this->manager->can('update', $this->admin));
        $this->assertFalse($this->manager->can('delete', $this->admin));
        $this->assertTrue($this->manager->can('update', $editor));
        $this->assertTrue($this->manager->can('delete', $editor));
        $this->assertTrue($this->admin->can('update', User::factory()->create()->assignRole('admin')));

        $this->actingAs($this->manager);
        $this->get("/admin/users/{$this->admin->id}/edit")->assertForbidden();
        $this->get("/admin/users/{$editor->id}/edit")->assertOk();
    }

    public function test_the_admin_role_is_not_offered_to_a_manager(): void
    {
        $adminRoleId = Role::findByName('admin')->id;
        $editor = $this->editor();

        $this->actingAs($this->manager);
        Livewire::test(EditUser::class, ['record' => $editor->getRouteKey()])
            ->assertFormFieldExists('roles', fn (Select $field) => ! array_key_exists($adminRoleId, $field->getOptions())
                && array_key_exists(Role::findByName('editor')->id, $field->getOptions()));

        $this->actingAs($this->admin);
        Livewire::test(EditUser::class, ['record' => $editor->getRouteKey()])
            ->assertFormFieldExists('roles', fn (Select $field) => array_key_exists($adminRoleId, $field->getOptions()));
    }

    public function test_a_manager_cannot_attach_the_admin_role_with_tampered_state(): void
    {
        $adminRoleId = Role::findByName('admin')->id;
        $editor = $this->editor();
        $this->actingAs($this->manager);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Sneaky',
                'email' => 'sneaky@example.test',
                'send_invitation' => false,
                'password' => 'a-strong-password-123',
                'roles' => [$adminRoleId],
            ])
            ->call('create')
            ->assertHasFormErrors(['roles']);
        $this->assertNull(User::where('email', 'sneaky@example.test')->first());

        Livewire::test(EditUser::class, ['record' => $editor->getRouteKey()])
            ->fillForm(['roles' => [$adminRoleId, Role::findByName('editor')->id]])
            ->call('save')
            ->assertHasFormErrors(['roles']);
        $this->assertFalse($editor->fresh()->hasRole('admin'));
    }

    public function test_an_administrator_can_still_give_the_admin_role(): void
    {
        $editor = $this->editor();
        $this->actingAs($this->admin);

        Livewire::test(EditUser::class, ['record' => $editor->getRouteKey()])
            ->fillForm(['roles' => [Role::findByName('admin')->id]])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertTrue($editor->fresh()->hasRole('admin'));
    }

    public function test_a_manager_cannot_send_a_password_link_to_or_lock_an_administrator(): void
    {
        Notification::fake();
        $editor = $this->editor();
        $this->actingAs($this->manager);

        $list = Livewire::test(ListUsers::class);
        foreach (['sendAccessLink', 'requirePassword', 'toggleActive'] as $action) {
            // Hidden, and a hand-crafted request to run it does nothing.
            $list->assertTableActionHidden($action, $this->admin)
                ->call('mountTableAction', $action, (string) $this->admin->getKey())
                ->call('callMountedTableAction');
        }
        $list->callTableAction('sendAccessLink', $editor);

        Notification::assertNotSentTo($this->admin, StaffAccessNotification::class);
        Notification::assertSentTo($editor, StaffAccessNotification::class);
        $this->admin->refresh();
        $this->assertTrue($this->admin->is_active);
        $this->assertFalse($this->admin->password_change_required);
    }

    public function test_bulk_delete_skips_administrators_and_the_signed_in_account(): void
    {
        $editor = $this->editor();
        $this->actingAs($this->manager);

        Livewire::test(ListUsers::class)
            ->callTableBulkAction('delete', [$this->admin, $editor, $this->manager]);

        $this->assertModelExists($this->admin);
        $this->assertModelExists($this->manager);
        $this->assertModelMissing($editor);

        // Administrators pass every policy, but still never delete themselves.
        $other = $this->editor();
        $this->actingAs($this->admin);
        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('delete', $this->admin)
            ->callTableBulkAction('delete', [$this->admin, $other]);

        $this->assertModelExists($this->admin);
        $this->assertModelMissing($other);
    }
}

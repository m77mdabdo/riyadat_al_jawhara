<?php

namespace App\Actions\SiteContent;

use App\Enums\UserRole;
use App\Support\Permissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the permissions the policies check and gives the built-in editor
 * role exactly what editors could always do, so switching the policies to
 * permissions changes nothing. Inserts only: a role that already has
 * permissions (edited by an admin) is left alone.
 */
class ImportPermissions
{
    public function handle(): int
    {
        $created = 0;
        foreach (Permissions::all() as $name) {
            $created += (int) Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web'])->wasRecentlyCreated;
        }

        foreach (UserRole::values() as $name) {
            Role::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $editor = Role::findByName(UserRole::Editor->value, 'web');
        if ($editor->permissions()->doesntExist()) {
            $editor->givePermissionTo(Permissions::editorDefaults());
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $created;
    }
}

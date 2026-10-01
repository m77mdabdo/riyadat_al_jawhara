<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Roles: managed by whoever manages users. The built-in admin and editor
 * roles cannot be deleted (or renamed), and neither can a role in use.
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function update(User $user, Role $role): bool
    {
        return $role->name !== UserRole::Admin->value && $user->hasPermission('users.manage');
    }

    public function delete(User $user, Role $role): bool
    {
        return ! in_array($role->name, UserRole::values(), true) && ! $role->users()->exists() && $user->hasPermission('users.manage');
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}

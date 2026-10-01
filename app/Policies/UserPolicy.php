<?php

namespace App\Policies;

use App\Models\User;

/**
 * Accounts and roles: admins, or the users.manage permission. Nobody
 * deletes or deactivates their own account.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('users.manage') && $user->isNot($model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }
}

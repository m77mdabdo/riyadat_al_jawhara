<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/**
 * Accounts and roles: admins, or the users.manage permission. Nobody
 * deletes or deactivates their own account, and only an administrator may
 * change or delete an administrator's account (admins pass every check in
 * Gate::before, so these rules bind the users.manage holders).
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
        return $user->hasPermission('users.manage') && $this->mayTouch($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('users.manage') && $user->isNot($model) && $this->mayTouch($user, $model);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    private function mayTouch(User $user, User $model): bool
    {
        return $user->hasRole(UserRole::Admin->value) || ! $model->hasRole(UserRole::Admin->value);
    }
}

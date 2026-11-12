<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Shared rules for everything editors manage in the admin panel. Once roles
 * have permissions (Administration → Roles, seeded by app:import-site-content)
 * the "{area}.{ability}" permission decides; until then the built-in rules
 * apply: staff may view, create and edit; only admins delete. Admins pass
 * every check through the Gate::before hook in AppServiceProvider.
 */
abstract class ContentPolicy
{
    /** Permission prefix: FaqPolicy => "faqs", StoneTypePolicy => "stone_types". */
    protected function area(): string
    {
        return Str::of(class_basename(static::class))->beforeLast('Policy')->snake()->plural()->toString();
    }

    protected function allows(User $user, string $ability, bool $builtIn): bool
    {
        return $user->usesPermissions() ? $user->hasPermission($this->area().'.'.$ability) : $builtIn;
    }

    protected function isStaff(User $user): bool
    {
        return $user->hasAnyRole(UserRole::values());
    }

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view', $this->isStaff($user));
    }

    public function view(User $user, Model $record): bool
    {
        return $this->allows($user, 'view', $this->isStaff($user));
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create', $this->isStaff($user));
    }

    public function update(User $user, ?Model $record = null): bool
    {
        return $this->allows($user, 'update', $this->isStaff($user));
    }

    /** Duplicating a record creates a new one. */
    public function replicate(User $user, Model $record): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->allows($user, 'delete', false);
    }

    public function deleteAny(User $user): bool
    {
        return $this->allows($user, 'delete', false);
    }

    public function restore(User $user, Model $record): bool
    {
        return $this->allows($user, 'delete', false);
    }

    public function restoreAny(User $user): bool
    {
        return $this->allows($user, 'delete', false);
    }

    /** Permanent deletion stays with administrators. */
    public function forceDelete(User $user, Model $record): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}

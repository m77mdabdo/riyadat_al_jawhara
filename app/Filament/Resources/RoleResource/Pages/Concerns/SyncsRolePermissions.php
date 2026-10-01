<?php

namespace App\Filament\Resources\RoleResource\Pages\Concerns;

use App\Support\Permissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * The role form shows permissions as checkboxes per area ("abilities") and
 * a list of special ones; these helpers convert to and from permission names.
 */
trait SyncsRolePermissions
{
    /** @var list<string> */
    protected array $permissionNames = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function splitPermissions(array $data): array
    {
        $names = [];
        foreach ((array) ($data['abilities'] ?? []) as $area => $abilities) {
            foreach ((array) $abilities as $ability) {
                $names[] = "{$area}.{$ability}";
            }
        }
        $names = [...$names, ...(array) ($data['special'] ?? [])];
        $this->permissionNames = array_values(array_intersect($names, Permissions::all()));
        unset($data['abilities'], $data['special']);

        return $data;
    }

    protected function syncPermissions(Role $role): void
    {
        foreach ($this->permissionNames as $name) {
            Permission::findOrCreate($name, 'web');
        }
        $role->syncPermissions($this->permissionNames);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return array{abilities: array<string, list<string>>, special: list<string>}
     */
    protected static function permissionState(Role $role): array
    {
        $abilities = [];
        $special = [];
        foreach ($role->permissions->pluck('name') as $name) {
            if (in_array($name, Permissions::SPECIAL, true)) {
                $special[] = $name;
            } elseif (str_contains($name, '.')) {
                [$area, $ability] = explode('.', $name, 2);
                $abilities[$area][] = $ability;
            }
        }

        return ['abilities' => $abilities, 'special' => $special];
    }
}

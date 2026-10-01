<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Filament\Resources\RoleResource\Pages\Concerns\SyncsRolePermissions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Role;

/**
 * @property Role $record
 */
class CreateRole extends CreateRecord
{
    use SyncsRolePermissions;

    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->splitPermissions($data) + ['guard_name' => 'web'];
    }

    protected function afterCreate(): void
    {
        $this->syncPermissions($this->record);
    }
}

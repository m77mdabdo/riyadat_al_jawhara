<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Filament\Resources\RoleResource\Pages\Concerns\SyncsRolePermissions;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

/**
 * @property Role $record
 */
class EditRole extends EditRecord
{
    use SyncsRolePermissions;

    protected static string $resource = RoleResource::class;

    public function getTitle(): string
    {
        return __('Role').': '.RoleResource::roleLabel($this->record->name);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data + self::permissionState($this->record);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->splitPermissions($data);
    }

    protected function afterSave(): void
    {
        $this->syncPermissions($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}

<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use App\Models\Menu;
use Filament\Resources\Pages\EditRecord;

/**
 * @property Menu $record
 */
class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    public function getTitle(): string
    {
        return __('Menu').': '.$this->record->location->getLabel();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

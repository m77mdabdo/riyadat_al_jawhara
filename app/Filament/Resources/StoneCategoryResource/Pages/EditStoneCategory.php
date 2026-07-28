<?php

namespace App\Filament\Resources\StoneCategoryResource\Pages;

use App\Filament\Resources\StoneCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStoneCategory extends EditRecord
{
    protected static string $resource = StoneCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

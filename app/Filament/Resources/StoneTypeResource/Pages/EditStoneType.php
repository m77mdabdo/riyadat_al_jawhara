<?php

namespace App\Filament\Resources\StoneTypeResource\Pages;

use App\Filament\Resources\StoneTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStoneType extends EditRecord
{
    protected static string $resource = StoneTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

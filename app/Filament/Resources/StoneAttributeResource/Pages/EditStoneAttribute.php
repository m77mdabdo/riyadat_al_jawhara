<?php

namespace App\Filament\Resources\StoneAttributeResource\Pages;

use App\Filament\Resources\StoneAttributeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStoneAttribute extends EditRecord
{
    protected static string $resource = StoneAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

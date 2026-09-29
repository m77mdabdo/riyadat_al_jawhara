<?php

namespace App\Filament\Resources\StoneAttributeResource\Pages;

use App\Filament\Resources\StoneAttributeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStoneAttributes extends ListRecords
{
    protected static string $resource = StoneAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

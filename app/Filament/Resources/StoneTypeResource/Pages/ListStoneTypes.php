<?php

namespace App\Filament\Resources\StoneTypeResource\Pages;

use App\Filament\Resources\StoneTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStoneTypes extends ListRecords
{
    protected static string $resource = StoneTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

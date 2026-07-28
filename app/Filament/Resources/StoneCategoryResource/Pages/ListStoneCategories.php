<?php

namespace App\Filament\Resources\StoneCategoryResource\Pages;

use App\Filament\Resources\StoneCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStoneCategories extends ListRecords
{
    protected static string $resource = StoneCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

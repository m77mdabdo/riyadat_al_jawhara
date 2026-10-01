<?php

namespace App\Filament\Resources\StoneCategoryResource\Pages;

use App\Filament\Concerns\EditsPublicRecord;
use App\Filament\Resources\StoneCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditStoneCategory extends EditRecord
{
    use EditsPublicRecord;

    protected static string $resource = StoneCategoryResource::class;
}

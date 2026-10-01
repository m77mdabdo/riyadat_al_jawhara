<?php

namespace App\Filament\Resources\StoneTypeResource\Pages;

use App\Filament\Concerns\EditsPublicRecord;
use App\Filament\Resources\StoneTypeResource;
use Filament\Resources\Pages\EditRecord;

class EditStoneType extends EditRecord
{
    use EditsPublicRecord;

    protected static string $resource = StoneTypeResource::class;
}

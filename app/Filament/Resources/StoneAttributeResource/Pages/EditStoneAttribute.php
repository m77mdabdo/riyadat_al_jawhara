<?php

namespace App\Filament\Resources\StoneAttributeResource\Pages;

use App\Filament\Concerns\EditsPublicRecord;
use App\Filament\Resources\StoneAttributeResource;
use Filament\Resources\Pages\EditRecord;

class EditStoneAttribute extends EditRecord
{
    use EditsPublicRecord;

    protected static string $resource = StoneAttributeResource::class;
}

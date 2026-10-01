<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Concerns\EditsPublicRecord;
use App\Filament\Resources\TagResource;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    use EditsPublicRecord;

    protected static string $resource = TagResource::class;
}

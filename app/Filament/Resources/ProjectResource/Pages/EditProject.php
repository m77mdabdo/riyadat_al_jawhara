<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Concerns\EditsPublicRecord;
use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    use EditsPublicRecord;

    protected static string $resource = ProjectResource::class;
}

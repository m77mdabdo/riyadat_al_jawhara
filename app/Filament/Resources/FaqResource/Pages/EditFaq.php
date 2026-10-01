<?php

namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Concerns\EditsPublicRecord;
use App\Filament\Resources\FaqResource;
use Filament\Resources\Pages\EditRecord;

class EditFaq extends EditRecord
{
    use EditsPublicRecord;

    protected static string $resource = FaqResource::class;
}

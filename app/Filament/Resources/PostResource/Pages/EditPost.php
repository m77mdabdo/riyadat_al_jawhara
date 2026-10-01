<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Concerns\EditsPublicRecord;
use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    use EditsPublicRecord;

    protected static string $resource = PostResource::class;
}

<?php

namespace App\Filament\Resources\BlogCategoryResource\Pages;

use App\Filament\Concerns\EditsPublicRecord;
use App\Filament\Resources\BlogCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditBlogCategory extends EditRecord
{
    use EditsPublicRecord;

    protected static string $resource = BlogCategoryResource::class;
}

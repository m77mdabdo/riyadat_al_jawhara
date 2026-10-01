<?php

namespace App\Filament\Resources\SiteImageResource\Pages;

use App\Filament\Resources\SiteImageResource;
use Filament\Resources\Pages\ListRecords;

class ListSiteImages extends ListRecords
{
    protected static string $resource = SiteImageResource::class;

    protected function getHeaderActions(): array
    {
        return [SiteImageResource::missingAction()];
    }
}

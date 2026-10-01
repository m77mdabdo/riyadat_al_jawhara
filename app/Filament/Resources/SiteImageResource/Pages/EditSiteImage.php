<?php

namespace App\Filament\Resources\SiteImageResource\Pages;

use App\Filament\Resources\SiteImageResource;
use App\Models\SiteImage;
use App\Support\SiteImages;
use Filament\Resources\Pages\EditRecord;

/**
 * @property SiteImage $record
 */
class EditSiteImage extends EditRecord
{
    protected static string $resource = SiteImageResource::class;

    public function getTitle(): string
    {
        return $this->record->label();
    }

    protected function afterSave(): void
    {
        // The upload may change without the row changing.
        SiteImages::flush();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

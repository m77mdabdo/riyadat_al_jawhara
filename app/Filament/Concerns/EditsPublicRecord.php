<?php

namespace App\Filament\Concerns;

use App\Models\Contracts\HasPublicUrl;
use Filament\Actions;

/**
 * Header actions for edit pages of public records: View on site, delete,
 * restore and (admins) force delete.
 */
trait EditsPublicRecord
{
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewOnSite')
                ->label(__('View on site'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (): ?string => $this->record instanceof HasPublicUrl ? $this->record->publicUrl() : null, shouldOpenInNewTab: true)
                ->visible(fn (): bool => ! ($this->record->deleted_at ?? null) && $this->record instanceof HasPublicUrl && $this->record->publicUrl() !== null),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }
}

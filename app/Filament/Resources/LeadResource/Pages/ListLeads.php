<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Resources\LeadResource;
use App\Support\LeadCsvExporter;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Exports exactly what the table currently shows (search + filters).
            // Leads are personal data, so export is limited to admins.
            Actions\Action::make('exportCsv')
                ->label(__('Export CSV'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn (): bool => auth()->user()?->hasRole('admin') ?? false)
                ->action(fn () => LeadCsvExporter::download($this->getFilteredTableQuery())),
            Actions\CreateAction::make(),
        ];
    }
}

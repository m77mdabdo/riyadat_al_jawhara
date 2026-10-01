<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Actions\Leads\ExportLeadsCsv;
use App\Enums\LeadStatus;
use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

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
                ->visible(fn (): bool => auth()->user()?->can('export', Lead::class) ?? false)
                ->action(fn () => app(ExportLeadsCsv::class)->handle($this->getFilteredTableQuery())),
            Actions\CreateAction::make(),
        ];
    }

    /**
     * One tab per status (with counts), like the columns of a board.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $counts = Lead::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $tabs = ['all' => Tab::make(__('All'))->badge(array_sum($counts->all()) ?: null)];

        foreach (LeadStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->badge($counts[$status->value] ?? null)
                ->badgeColor($status->getColor())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status->value));
        }

        return $tabs;
    }
}

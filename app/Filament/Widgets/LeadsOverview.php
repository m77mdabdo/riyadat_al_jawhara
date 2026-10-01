<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Support\Dashboard;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Headline figures with trends (cached for 5 minutes).
 */
class LeadsOverview extends BaseWidget
{
    /** Figures are cached for minutes; reloading the page is enough. */
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', Lead::class) ?? false;
    }

    protected function getStats(): array
    {
        $kpi = Dashboard::kpis();
        $days = (int) config('site.dashboard_whatsapp_days');

        return [
            Stat::make(__('New Leads'), $kpi['new'])
                ->description(__('Awaiting first contact'))
                ->color('warning')
                ->url(route('filament.admin.resources.leads.index', ['tableFilters[status][value]' => 'new'])),
            Stat::make(__('Follow-ups Due'), $kpi['due'])
                ->description($kpi['due'] > 0 ? __('Due today or overdue') : __('Nothing overdue'))
                ->color($kpi['due'] > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.leads.index', ['tableFilters[follow_up_due][isActive]' => true])),
            $this->trend(Stat::make(__('Leads this week'), $kpi['leads_week']), $kpi['leads_week'], $kpi['leads_prev_week'], __('vs the week before'))
                ->chart(array_map('floatval', array_values(Dashboard::leadsPerDay(14)))),
            $this->trend(Stat::make(__('WhatsApp Clicks (:days days)', ['days' => $days]), $kpi['whatsapp']), $kpi['whatsapp'], $kpi['whatsapp_prev'], __('vs the :days days before', ['days' => $days]))
                ->description($kpi['whatsapp_leads'].' '.__('with a known page context → leads')),
            Stat::make(__('Total Leads'), $kpi['total'])
                ->description(__('All time'))
                ->color('success'),
            Stat::make(__('Published Projects'), $kpi['projects'])
                ->description(__('Visible in the portfolio'))
                ->color('primary'),
        ];
    }

    private function trend(Stat $stat, int $current, int $previous, string $compared): Stat
    {
        $change = Dashboard::change($current, $previous);
        if ($change === null) {
            return $stat->color('gray');
        }

        return $stat
            ->description(($change >= 0 ? '+' : '').$change.'% '.$compared)
            ->descriptionIcon($change >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($change >= 0 ? 'success' : 'danger');
    }
}

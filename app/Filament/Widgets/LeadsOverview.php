<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\Project;
use App\Models\WhatsappClick;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $due = Lead::followUpDue()->count();

        return [
            Stat::make(__('New Leads'), Lead::where('status', 'new')->count())
                ->description(__('Awaiting first contact'))
                ->color('warning'),
            Stat::make(__('Follow-ups Due'), $due)
                ->description($due > 0 ? __('Due today or overdue') : __('Nothing overdue'))
                ->color($due > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.leads.index', ['tableFilters[follow_up_due][isActive]' => true])),
            Stat::make(__('WhatsApp Clicks (30 days)'), WhatsappClick::where('created_at', '>=', now()->subDays(30))->count())
                ->description(Lead::where('source', 'whatsapp')->where('created_at', '>=', now()->subDays(30))->count().' '.__('with a known page context → leads'))
                ->color('success'),
            Stat::make(__('Total Leads'), Lead::count())
                ->description(__('All time'))
                ->color('success'),
            Stat::make(__('Published Projects'), Project::active()->count())
                ->description(__('Visible in the portfolio'))
                ->color('primary'),
        ];
    }
}

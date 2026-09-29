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
            Stat::make('New Leads', Lead::where('status', 'new')->count())
                ->description('Awaiting first contact')
                ->color('warning'),
            Stat::make('Follow-ups Due', $due)
                ->description($due > 0 ? 'Due today or overdue' : 'Nothing overdue')
                ->color($due > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.leads.index', ['tableFilters[follow_up_due][isActive]' => true])),
            Stat::make('WhatsApp Clicks (30 days)', WhatsappClick::where('created_at', '>=', now()->subDays(30))->count())
                ->description(Lead::where('source', 'whatsapp')->where('created_at', '>=', now()->subDays(30))->count().' with a known page context → leads')
                ->color('success'),
            Stat::make('Total Leads', Lead::count())
                ->description('All time')
                ->color('success'),
            Stat::make('Published Projects', Project::active()->count())
                ->description('Visible in the portfolio')
                ->color('primary'),
        ];
    }
}

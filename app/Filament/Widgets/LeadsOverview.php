<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('New Leads', Lead::where('status', 'new')->count())
                ->description('Awaiting first contact')
                ->color('warning'),
            Stat::make('Total Leads', Lead::count())
                ->description('All time')
                ->color('success'),
            Stat::make('Total Projects', Project::count())
                ->description('Published portfolio items')
                ->color('primary'),
        ];
    }
}

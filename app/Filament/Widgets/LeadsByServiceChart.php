<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\Service;
use App\Support\Dashboard;
use Filament\Widgets\ChartWidget;

/**
 * Which services the last 90 days of requests were about.
 */
class LeadsByServiceChart extends ChartWidget
{
    /** Figures are cached for minutes; reloading the page is enough. */
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', Lead::class) ?? false;
    }

    public function getHeading(): string
    {
        return __('Leads by service (90 days)');
    }

    protected function getData(): array
    {
        $counts = Dashboard::leadsByService();
        $names = Service::withTrashed()->whereIn('id', array_filter(array_keys($counts), 'is_int'))->get()->keyBy('id');

        return [
            'datasets' => [[
                'label' => __('Leads'),
                'data' => array_values($counts),
                'backgroundColor' => ['#2B2B2B', '#B08D57', '#8B8680', '#C9A86A', '#5C5C5C', '#D8CBB5', '#1C1C1C', '#E0E0E0'],
            ]],
            'labels' => array_map(fn ($id) => $names[$id]->name ?? __('Not specified'), array_keys($counts)),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

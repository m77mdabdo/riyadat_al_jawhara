<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\StoneType;
use App\Support\Dashboard;
use Filament\Widgets\ChartWidget;

/**
 * The five stones most often asked about.
 */
class TopStonesChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', Lead::class) ?? false;
    }

    public function getHeading(): string
    {
        return __('Top 5 stones in requests');
    }

    protected function getData(): array
    {
        $counts = Dashboard::topStones();
        $names = StoneType::withTrashed()->whereIn('id', array_keys($counts))->get()->keyBy('id');

        return [
            'datasets' => [['label' => __('Leads'), 'data' => array_values($counts), 'backgroundColor' => '#B08D57']],
            'labels' => array_map(fn ($id) => $names[$id]->name ?? '—', array_keys($counts)),
        ];
    }

    protected function getOptions(): array
    {
        return ['indexAxis' => 'y', 'plugins' => ['legend' => ['display' => false]]];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

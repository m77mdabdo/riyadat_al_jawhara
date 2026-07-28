<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;

class LeadsChart extends ChartWidget
{
    protected static ?string $heading = 'Leads per Month';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => now()->subMonths($i)->startOfMonth());

        $counts = $months->map(
            fn ($month) => Lead::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count()
        );

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $counts->values(),
                    'backgroundColor' => '#2B2B2B',
                    'borderColor' => '#2B2B2B',
                ],
            ],
            'labels' => $months->map(fn ($month) => $month->format('M Y'))->values(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

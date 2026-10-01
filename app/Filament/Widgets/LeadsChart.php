<?php

namespace App\Filament\Widgets;

use App\Enums\LeadSource;
use App\Models\Lead;
use App\Support\Dashboard;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Leads per day for the last 30 days, website form vs WhatsApp.
 */
class LeadsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return auth()->user()?->can('viewAny', Lead::class) ?? false;
    }

    public function getHeading(): string
    {
        return __('Leads per day (30 days)');
    }

    protected function getData(): array
    {
        $website = Dashboard::leadsPerDay(30, LeadSource::Website);
        $whatsapp = Dashboard::leadsPerDay(30, LeadSource::Whatsapp);

        return [
            'datasets' => [
                ['label' => LeadSource::Website->getLabel(), 'data' => array_values($website), 'borderColor' => '#2B2B2B', 'backgroundColor' => '#2B2B2B'],
                ['label' => LeadSource::Whatsapp->getLabel(), 'data' => array_values($whatsapp), 'borderColor' => '#1DA851', 'backgroundColor' => '#1DA851'],
            ],
            'labels' => array_map(fn (string $day) => Carbon::parse($day)->translatedFormat('j M'), array_keys($website)),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

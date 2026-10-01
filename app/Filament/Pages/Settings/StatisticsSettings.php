<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Pages\Concerns\IsSettingsPage;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

/**
 * Settings → Statistics: the "In numbers" figures on the home page.
 */
class StatisticsSettings extends Page implements HasForms
{
    use IsSettingsPage;

    protected static ?int $navigationSort = 70;

    protected static ?string $slug = 'settings/statistics';

    public static function label(): string
    {
        return __('Statistics');
    }

    /**
     * @return array<int, Component>
     */
    protected function schema(): array
    {
        return [
            Forms\Components\Section::make(__('Statistics (home page)'))
                ->description(__('Real figures only. Leave a field empty to hide it; the section is hidden when all are empty.'))
                ->schema([
                    Forms\Components\TextInput::make('stat_projects_completed')->label(__('Projects completed'))->numeric()->minValue(1)->maxValue(100000),
                    Forms\Components\TextInput::make('stat_years_experience')->label(__('Years of experience'))->numeric()->minValue(1)->maxValue(200),
                    Forms\Components\TextInput::make('stat_cities_served')->label(__('Cities served'))->numeric()->minValue(1)->maxValue(1000),
                    Forms\Components\TextInput::make('stat_clients_served')->label(__('Clients served'))->numeric()->minValue(1)->maxValue(1000000),
                ])->columns(4),
        ];
    }
}

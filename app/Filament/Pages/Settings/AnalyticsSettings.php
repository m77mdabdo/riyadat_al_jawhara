<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Pages\Concerns\IsSettingsPage;
use App\Filament\Support\OptionToggle;
use App\Support\Analytics;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

/**
 * Settings → Analytics: Google Analytics 4 or Plausible. Nothing loads until
 * the visitor accepts the cookie banner (always shown when a provider is set).
 */
class AnalyticsSettings extends Page implements HasForms
{
    use IsSettingsPage;

    protected static ?int $navigationSort = 50;

    protected static ?string $slug = 'settings/analytics';

    public static function label(): string
    {
        return __('Analytics');
    }

    /**
     * @return array<int, Component>
     */
    protected function schema(): array
    {
        return [
            Forms\Components\Section::make(__('Analytics'))
                ->description(__('Visitors are asked for consent first; without “Accept”, no analytics script is loaded. Leave both empty to switch analytics off.'))
                ->schema([
                    OptionToggle::make('analytics_options.disabled', false)
                        ->label(__('Switch analytics off completely'))
                        ->helperText(__('Also ignores IDs set by the developer in the server configuration.')),
                    Forms\Components\TextInput::make('analytics_options.ga4_id')
                        ->label(__('Google Analytics 4 measurement ID'))
                        ->placeholder(Analytics::GA4_EXAMPLE)
                        ->regex(Analytics::GA4_PATTERN)
                        ->maxLength(30)
                        ->extraInputAttributes(['dir' => 'ltr']),
                    Forms\Components\TextInput::make('analytics_options.plausible_domain')
                        ->label(__('Plausible site domain'))
                        ->placeholder(Analytics::DOMAIN_EXAMPLE)
                        ->regex(Analytics::DOMAIN_PATTERN)
                        ->maxLength(120)
                        ->extraInputAttributes(['dir' => 'ltr']),
                    Forms\Components\Placeholder::make('status')
                        ->label(__('Now'))
                        ->content(fn () => Analytics::enabled() ? __('On (with consent banner)') : __('Off')),
                ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function mutateSaveData(array $state): array
    {
        return ['analytics_options' => $state['analytics_options'] ?? []];
    }
}

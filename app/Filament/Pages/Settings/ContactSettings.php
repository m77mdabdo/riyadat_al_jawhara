<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Pages\Concerns\IsSettingsPage;
use App\Filament\Support\OptionToggle;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

/**
 * Settings → Contact: address, opening hours and the map on the contact page.
 */
class ContactSettings extends Page implements HasForms
{
    use IsSettingsPage;

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'settings/contact';

    public static function label(): string
    {
        return __('Contact & map');
    }

    /**
     * @return array<int, Component>
     */
    protected function schema(): array
    {
        return [
            Forms\Components\Section::make(__('Address & Hours'))
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('address_ar')->label(__('Address (AR)'))->maxLength(255)->extraInputAttributes(['dir' => 'rtl']),
                        Forms\Components\TextInput::make('address_en')->label(__('Address (EN)'))->maxLength(255),
                        Forms\Components\TextInput::make('working_hours_ar')->label(__('Working Hours (AR)'))->maxLength(255)->extraInputAttributes(['dir' => 'rtl']),
                        Forms\Components\TextInput::make('working_hours_en')->label(__('Working Hours (EN)'))->maxLength(255),
                    ]),
                ]),
            Forms\Components\Section::make(__('Map'))
                ->description(__('Copy the latitude and longitude from Google Maps (right-click the location). Leave empty to hide the map.'))
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('map_lat')->label(__('Map Latitude'))->numeric()->minValue(-90)->maxValue(90)->live(onBlur: true),
                        Forms\Components\TextInput::make('map_lng')->label(__('Map Longitude'))->numeric()->minValue(-180)->maxValue(180)->live(onBlur: true),
                    ]),
                    Forms\Components\Placeholder::make('map_preview')
                        ->label(__('Preview'))
                        ->visible(fn (Get $get) => is_numeric($get('map_lat')) && is_numeric($get('map_lng')))
                        ->content(fn (Get $get) => new HtmlString(sprintf(
                            '<iframe title="%s" src="https://maps.google.com/maps?q=%F,%F&z=14&output=embed" style="width:100%%;height:280px;border:0;border-radius:0.75rem" loading="lazy"></iframe>',
                            e(__('Preview')), (float) $get('map_lat'), (float) $get('map_lng'),
                        ))),
                    OptionToggle::make('contact_options.show_map')->label(__('Show the map on the contact page')),
                    OptionToggle::make('contact_options.show_directions')->label(__('Show the “Get directions” button')),
                ]),
        ];
    }
}

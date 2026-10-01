<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Toggle;

/**
 * Toggle for a JSON option: when the option was never saved it shows (and
 * saves) its built-in default instead of "off", so opening and saving a page
 * never changes the site by accident.
 */
class OptionToggle
{
    public static function make(string $name, bool $default = true): Toggle
    {
        return Toggle::make($name)
            ->default($default)
            ->afterStateHydrated(function (Toggle $component, mixed $state) use ($default) {
                if ($state === null) {
                    $component->state($default);
                }
            });
    }
}

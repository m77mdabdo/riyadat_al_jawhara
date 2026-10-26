<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Str;

use function Filament\Support\get_model_label;

/**
 * Resource names, navigation groups and badge tooltips through the translator
 * (lang/ar.json), so the whole panel follows the admin's language. English keys
 * equal the labels Filament generated before, so the English panel is unchanged.
 */
trait TranslatesResourceLabels
{
    public static function getModelLabel(): string
    {
        return __(static::$modelLabel ?? get_model_label(static::getModel()));
    }

    public static function getPluralModelLabel(): string
    {
        return __(static::$pluralModelLabel ?? Str::plural(static::$modelLabel ?? get_model_label(static::getModel())));
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ? __(static::$navigationLabel) : static::getTitleCasePluralModelLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        return static::$navigationGroup ? __(static::$navigationGroup) : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return static::$navigationBadgeTooltip ? __(static::$navigationBadgeTooltip) : null;
    }
}

<?php

namespace App\Filament\Support;

/**
 * Admin-side helper for bilingual columns: pick the `_ar` or `_en` column that
 * matches the current admin locale, so relation labels, option lists and
 * table columns follow the language the admin chose.
 */
class Localized
{
    public static function column(string $base): string
    {
        return $base.'_'.(app()->getLocale() === 'ar' ? 'ar' : 'en');
    }

    /**
     * Translates the labels of a value => English label map (select options).
     *
     * @param  array<string, string>  $options
     * @return array<string, string>
     */
    public static function options(array $options): array
    {
        return array_map(fn (string $label): string => __($label), $options);
    }
}

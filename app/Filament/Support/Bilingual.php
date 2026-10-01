<?php

namespace App\Filament\Support;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Arabic / English tabs for bilingual fields ({base}_ar / {base}_en).
 *
 * - Each tab shows a warning badge with the number of fields that are filled
 *   in the other language but empty in this one (based on the saved record).
 * - Each field gets a "Copy from English/Arabic" hint action.
 * - The English field named in $slugFrom fills the slug while creating, or
 *   while the slug is still empty (editing a title never changes a live URL).
 */
class Bilingual
{
    /**
     * @param  array<string, Closure(string $locale): Field>  $fields  base => factory for one locale
     */
    public static function tabs(array $fields, ?string $slugFrom = null): Tabs
    {
        return Tabs::make('translations')
            ->columnSpanFull()
            ->tabs([
                self::tab('ar', $fields, $slugFrom),
                self::tab('en', $fields, $slugFrom),
            ]);
    }

    /**
     * @param  array<string, Closure(string $locale): Field>  $fields
     */
    private static function tab(string $locale, array $fields, ?string $slugFrom): Tab
    {
        $other = $locale === 'ar' ? 'en' : 'ar';

        $components = [];
        foreach ($fields as $base => $factory) {
            $field = $factory($locale);
            $field->hintAction(
                Action::make("copy_{$base}_{$locale}")
                    ->label(__($other === 'ar' ? 'Copy from Arabic' : 'Copy from English'))
                    ->icon('heroicon-m-document-duplicate')
                    ->action(fn (Set $set, Get $get) => $set("{$base}_{$locale}", $get("{$base}_{$other}")))
            );

            if ($locale === 'en' && $base === $slugFrom) {
                $field->live(onBlur: true)->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation) {
                    if ($operation === 'create' || blank($get('slug'))) {
                        $set('slug', Str::slug((string) $state));
                    }
                });
            }

            $components[] = $field;
        }

        return Tab::make(__($locale === 'ar' ? 'Arabic' : 'English'))
            ->schema($components)
            ->badge(fn (?Model $record): ?int => self::missingCount($record, array_keys($fields), $locale, $other) ?: null)
            ->badgeColor('warning');
    }

    /**
     * Fields filled in $other but empty in $locale.
     *
     * @param  list<string>  $bases
     */
    public static function missingCount(?Model $record, array $bases, string $locale, string $other): int
    {
        if (! $record) {
            return 0;
        }

        return count(array_filter($bases, fn (string $base) => blank($record->getAttribute("{$base}_{$locale}")) && filled($record->getAttribute("{$base}_{$other}"))));
    }

    /**
     * Convenience for the common "label (AR)/(EN)" naming of existing keys.
     */
    public static function label(string $base, string $locale): string
    {
        return __($base.' ('.strtoupper($locale).')');
    }
}

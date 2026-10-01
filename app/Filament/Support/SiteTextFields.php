<?php

namespace App\Filament\Support;

use App\Filament\Pages\SiteTexts as SiteTextsPage;
use App\Support\SiteTexts;
use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Arabic/English inputs for public interface texts (App\Support\SiteTexts)
 * inside a contextual admin form: the shipped text is the placeholder, an
 * empty field keeps it. State lives under `site_texts.{encoded key}.{locale}`;
 * the page fills it with state() and saves it with save().
 */
class SiteTextFields
{
    public const STATE = 'site_texts';

    public static function make(string $key, ?string $label = null, bool $multiline = false): Grid
    {
        $field = function (string $locale) use ($key, $label, $multiline): Component {
            $name = self::STATE.'.'.SiteTextsPage::encode($key).'.'.$locale;
            $component = $multiline ? Textarea::make($name)->rows(2)->autosize() : TextInput::make($name);

            return $component
                ->label(($label ?? __('Text')).' ('.strtoupper($locale).')')
                ->placeholder(SiteTexts::default($key, $locale))
                ->maxLength(2000)
                ->extraInputAttributes(['dir' => $locale === 'ar' ? 'rtl' : 'ltr', 'lang' => $locale])
                ->rule(fn () => function (string $attribute, mixed $value, Closure $fail) use ($key, $locale) {
                    if ($problem = SiteTexts::problem($key, is_string($value) ? $value : null, $locale)) {
                        $fail($problem);
                    }
                });
        };

        return Grid::make(2)->schema([$field('ar'), $field('en')]);
    }

    /**
     * Current overrides for the given keys, to merge into the form fill data.
     *
     * @param  list<string>  $keys
     * @return array<string, array<string, array{ar: ?string, en: ?string}>>
     */
    public static function state(array $keys): array
    {
        $state = [];
        foreach ($keys as $key) {
            $state[SiteTextsPage::encode($key)] = ['ar' => SiteTexts::value($key, 'ar'), 'en' => SiteTexts::value($key, 'en')];
        }

        return [self::STATE => $state];
    }

    /**
     * Saves the site-text part of a form state and removes it from $state.
     *
     * @param  array<string, mixed>  $state
     */
    public static function save(array &$state): void
    {
        foreach ($state[self::STATE] ?? [] as $encoded => $values) {
            $key = SiteTextsPage::decode((string) $encoded);
            if (SiteTexts::isEditable($key)) {
                SiteTexts::put($key, $values['ar'] ?? null, $values['en'] ?? null);
            }
        }
        unset($state[self::STATE]);
    }
}

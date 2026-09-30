<?php

namespace App\Models\Concerns;

/**
 * Bilingual columns are stored as {field}_ar / {field}_en.
 *
 * A model lists its bilingual fields in `$localized` (field => value to return
 * when both languages are empty). Reading `$model->field` then returns the
 * current locale's value and falls back to the other language when that one
 * is empty, so a missing translation shows the other language instead of a
 * blank heading or paragraph.
 *
 * @property array<string, string|null> $localized
 */
trait HasLocalizedAttributes
{
    public function translated(string $field): ?string
    {
        [$primary, $fallback] = app()->getLocale() === 'ar' ? ['ar', 'en'] : ['en', 'ar'];

        $value = parent::getAttribute("{$field}_{$primary}");

        return filled($value) ? $value : parent::getAttribute("{$field}_{$fallback}");
    }

    /**
     * @param  mixed  $key
     */
    public function getAttribute($key): mixed
    {
        if (is_string($key) && array_key_exists($key, $this->localized)) {
            return $this->translated($key) ?? $this->localized[$key];
        }

        return parent::getAttribute($key);
    }

    /**
     * @return list<string>
     */
    public function localizedFields(): array
    {
        return array_keys($this->localized);
    }
}

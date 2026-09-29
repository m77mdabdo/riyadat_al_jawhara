<?php

namespace App\Models\Concerns;

/**
 * Bilingual columns are stored as {field}_ar / {field}_en. translated()
 * returns the value for the current locale and falls back to the other
 * language when that one is empty, so a missing translation shows the
 * other language instead of a blank heading or paragraph.
 */
trait HasTranslations
{
    public function translated(string $field): ?string
    {
        [$primary, $fallback] = app()->getLocale() === 'ar' ? ['ar', 'en'] : ['en', 'ar'];

        $value = $this->getAttribute("{$field}_{$primary}");

        return filled($value) ? $value : $this->getAttribute("{$field}_{$fallback}");
    }
}

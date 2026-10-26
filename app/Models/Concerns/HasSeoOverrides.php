<?php

namespace App\Models\Concerns;

/**
 * Optional per-page SEO overrides (meta_title_*, meta_description_*), with
 * the page's own title/description as fallback. Requires HasTranslations.
 */
trait HasSeoOverrides
{
    public const SEO_FIELDS = ['meta_title_ar', 'meta_title_en', 'meta_description_ar', 'meta_description_en'];

    public function initializeHasSeoOverrides(): void
    {
        $this->mergeFillable(self::SEO_FIELDS);
    }

    public function seoTitle(?string $fallback): ?string
    {
        return $this->translated('meta_title') ?: $fallback;
    }

    public function seoDescription(?string $fallback): ?string
    {
        return $this->translated('meta_description') ?: $fallback;
    }
}

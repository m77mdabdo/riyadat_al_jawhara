<?php

namespace App\Support;

use App\Enums\PageKey;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Editable parts of the fixed public pages (App\Models\Page), cached as one
 * collection. A page without a row behaves exactly as before.
 */
class PageContent
{
    public const CACHE_KEY = 'pages.content';

    public static function get(PageKey|string|null $key): ?Page
    {
        // Resolved on every page (also without a key) so the cache is always
        // warm and a page's query count doesn't depend on which page came first.
        $pages = app(self::CACHE_KEY);

        return $key === null ? null : $pages[$key instanceof PageKey ? $key->value : $key] ?? null;
    }

    /**
     * @return array<string, Page>
     */
    public static function load(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => Page::query()->with('media')->get()->keyBy(fn (Page $page) => $page->key->value)->all());
        } catch (Throwable) {
            return [];
        }
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        app()->forgetInstance(self::CACHE_KEY);
        FragmentCache::flush();
    }

    /**
     * Banner of a page (uploaded image), or null to use its texture band.
     *
     * @return array{url: string, srcset: string|null}|null
     */
    public static function banner(?Page $page): ?array
    {
        $media = $page?->getFirstMedia('banner');
        if (! $media) {
            return null;
        }

        $candidates = [];
        foreach (config('site.webp_widths') as $name => $width) {
            if ($media->hasGeneratedConversion($name)) {
                $candidates[$width] = $media->getUrl($name);
            }
        }

        return [
            'url' => $candidates !== [] ? end($candidates) : $media->getUrl(),
            'srcset' => count($candidates) > 1 ? collect($candidates)->map(fn ($u, $w) => "{$u} {$w}w")->implode(', ') : null,
        ];
    }

    public static function ogImage(?Page $page): ?string
    {
        $media = $page?->getFirstMedia('og_image');

        return $media ? ($media->hasGeneratedConversion('og') ? $media->getUrl('og') : $media->getUrl()) : null;
    }
}

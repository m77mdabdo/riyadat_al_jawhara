<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * URLs of the current page in a given language. Slugs are shared between
 * languages, so the same named route with a different {locale} is the
 * translated page.
 */
class LocalizedUrl
{
    /**
     * @return array<int, string>
     */
    public static function supported(): array
    {
        return config('locales.supported');
    }

    public static function default(): string
    {
        return config('locales.default');
    }

    public static function isSupported(?string $locale): bool
    {
        return in_array($locale, self::supported(), true);
    }

    /**
     * The current page in $locale.
     *
     * @param  array<int, string>|null  $keepQuery  Query keys to keep (null = keep all).
     *                                              The language switcher keeps everything
     *                                              (filters, search); canonical/hreflang keep
     *                                              only real pagination.
     */
    public static function current(string $locale, ?array $keepQuery = null): string
    {
        $route = Route::current();

        if (! $route || ! $route->getName() || ! in_array('locale', $route->parameterNames(), true)) {
            return url('/'.$locale);
        }

        $query = request()->query();
        if ($keepQuery !== null) {
            $query = array_intersect_key($query, array_flip($keepQuery));
        }
        if (isset($query['page']) && (int) $query['page'] <= 1) {
            unset($query['page']);
        }

        $parameters = array_merge($route->parameters(), ['locale' => $locale]);

        return route($route->getName(), $parameters + $query);
    }

    /**
     * Canonical URL of the current page in $locale: filters and search are
     * views of the same page, only ?page=N (N > 1) is part of the identity.
     */
    public static function canonical(string $locale): string
    {
        return self::current($locale, ['page']);
    }

    /** Un-prefixed paths that are not pages (assets, admin, system routes). */
    private const UNLOCALIZED = ['admin', 'livewire', 'storage', 'build', 'images', 'videos', 'lang', 'sitemap.xml', 'robots.txt', 'whatsapp-click', 'admin-locale'];

    /**
     * An admin-entered link (e.g. a slide button) in the visitor's language:
     * internal page paths like "/contact" or "https://this-site/contact" get
     * the locale prefix; external links, anchors, tel:/mailto: and paths that
     * already carry a locale are returned unchanged.
     */
    public static function link(?string $href, ?string $locale = null): ?string
    {
        if ($href === null || $href === '') {
            return $href;
        }

        $locale ??= app()->getLocale();
        $parts = parse_url($href);
        if ($parts === false || isset($parts['scheme']) && ! in_array($parts['scheme'], ['http', 'https'], true)) {
            return $href;
        }

        $sameHost = ! isset($parts['host']) || $parts['host'] === parse_url(url('/'), PHP_URL_HOST);
        $path = $parts['path'] ?? '';
        if (! $sameHost || ! str_starts_with($path, '/') || str_starts_with($href, '//')) {
            return $href;
        }

        $first = explode('/', trim($path, '/'))[0];
        if (self::isSupported($first) || in_array($first, self::UNLOCALIZED, true) || str_contains($first, '.')) {
            return $href;
        }

        return url('/'.$locale.rtrim($path, '/'))
            .(isset($parts['query']) ? '?'.$parts['query'] : '')
            .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }
}

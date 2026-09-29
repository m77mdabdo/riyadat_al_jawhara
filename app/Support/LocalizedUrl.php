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
}

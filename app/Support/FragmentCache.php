<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Cache for rendered, visitor-independent HTML/XML fragments (home sections,
 * sitemap). Every key embeds a shared version token; flush() replaces the
 * token, so one content change invalidates every fragment at once and the old
 * entries simply expire. Never use it for markup with forms, CSRF tokens or
 * per-visitor state.
 */
class FragmentCache
{
    public const VERSION_KEY = 'fragment-cache:version';

    /**
     * @param  Closure(): string  $render
     */
    public static function remember(string $key, Closure $render, ?int $ttl = null): string
    {
        return self::rememberValue($key, $render, $ttl);
    }

    /**
     * Same invalidation for computed data: any serializable value.
     *
     * @template T
     *
     * @param  Closure(): T  $build
     * @return T
     */
    public static function rememberValue(string $key, Closure $build, ?int $ttl = null): mixed
    {
        $version = Cache::rememberForever(self::VERSION_KEY, fn () => Str::random(12));

        // Host and locale are part of the key: fragments contain absolute URLs
        // and translated text.
        $scope = request()->getSchemeAndHttpHost().':'.app()->getLocale();

        return Cache::remember("fragment:{$version}:{$scope}:{$key}", $ttl ?? (int) config('site.cache.fragment_ttl'), $build);
    }

    public static function flush(): void
    {
        Cache::forget(self::VERSION_KEY);
    }
}

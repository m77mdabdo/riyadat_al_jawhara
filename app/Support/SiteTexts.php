<?php

namespace App\Support;

use App\Models\SiteText;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

/**
 * The override layer over lang/{ar,en}/site.php: admins change public texts
 * from the dashboard (Website Content → Site texts and the contextual pages),
 * everything they leave alone keeps the shipped text. Overrides are cached as
 * one array and merged in by OverridableTranslationLoader.
 */
class SiteTexts
{
    public const CACHE_KEY = 'site-texts.overrides';

    public const GROUP = 'site';

    /**
     * List items edited as repeaters on the Homepage page, not one by one.
     */
    public const MANAGED_ELSEWHERE = ['home.why_items.', 'process.steps.', 'home.heritage_items.'];

    /**
     * @return array<string, string> key without the "site." prefix => text
     */
    public static function overrides(string $locale): array
    {
        // Memoized per request (scoped binding, see AppServiceProvider).
        return app(self::CACHE_KEY)[$locale] ?? [];
    }

    /**
     * All overrides by locale, from the cache. Never throws: before the table
     * is migrated, or when the database is down (error pages), the shipped
     * texts are used.
     *
     * @return array<string, array<string, string>>
     */
    public static function loadCached(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => self::load());
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return array<string, array<string, string>>
     */
    private static function load(): array
    {
        $overrides = [];
        foreach (SiteText::query()->get(['key', 'value_ar', 'value_en']) as $text) {
            $key = Str::after($text->key, self::GROUP.'.');
            foreach (['ar' => $text->value_ar, 'en' => $text->value_en] as $locale => $value) {
                if (filled($value)) {
                    $overrides[$locale][$key] = $value;
                }
            }
        }

        return $overrides;
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        app()->forgetInstance(self::CACHE_KEY);
        FragmentCache::flush();

        // Groups this process already loaded must be read again.
        if (app()->resolved('translator')) {
            app('translator')->setLoaded([]);
        }
    }

    /**
     * Texts as shipped in lang/{locale}/site.php, flattened: "home.cta_title" => "...".
     *
     * @return array<string, string>
     */
    public static function defaults(string $locale): array
    {
        $path = lang_path("{$locale}/".self::GROUP.'.php');

        return array_filter(Arr::dot(is_file($path) ? require $path : []), 'is_string');
    }

    /**
     * Every key an admin may override (both languages ship it), grouped by its
     * first segment.
     *
     * @return array<string, list<string>> group => keys (with the "site." prefix)
     */
    public static function editableKeys(): array
    {
        $ar = self::defaults('ar');
        $groups = [];
        foreach (array_keys(self::defaults('en')) as $key) {
            if (! array_key_exists($key, $ar) || Str::startsWith($key, self::MANAGED_ELSEWHERE)) {
                continue;
            }
            $groups[Str::before($key, '.')][] = self::GROUP.'.'.$key;
        }

        return $groups;
    }

    public static function isEditable(string $key): bool
    {
        return in_array($key, Arr::flatten(self::editableKeys()), true);
    }

    public static function default(string $key, string $locale): ?string
    {
        return self::defaults($locale)[Str::after($key, self::GROUP.'.')] ?? null;
    }

    /**
     * The override saved for a key, or null.
     */
    public static function value(string $key, string $locale): ?string
    {
        return self::overrides($locale)[Str::after($key, self::GROUP.'.')] ?? null;
    }

    /**
     * Problem with an override, or null when it can be saved: every
     * :placeholder of the shipped text must survive, and a plural text
     * ("one|many") keeps its number of forms.
     */
    public static function problem(string $key, ?string $value, string $locale): ?string
    {
        $default = self::default($key, $locale);
        if (blank($value) || $default === null) {
            return null;
        }

        $missing = array_diff(self::placeholders($default), self::placeholders($value));
        if ($missing !== []) {
            return __('Keep the placeholders :list exactly as they are.', ['list' => implode(', ', array_map(fn ($p) => ':'.$p, $missing))]);
        }

        if (substr_count($default, '|') !== substr_count($value, '|')) {
            return __('Keep the same number of "|"-separated forms as the original text.');
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function placeholders(string $text): array
    {
        preg_match_all('/:([a-z_]+)/i', $text, $matches);

        return array_values(array_unique($matches[1]));
    }

    /**
     * Save (or clear) an override. A value equal to the shipped text or empty
     * is stored as "no override". Plain text only.
     */
    public static function put(string $key, ?string $ar, ?string $en): void
    {
        $clean = function (?string $value, string $locale) use ($key): ?string {
            $value = trim(strip_tags((string) $value));

            return $value === '' || $value === self::default($key, $locale) ? null : $value;
        };
        $ar = $clean($ar, 'ar');
        $en = $clean($en, 'en');

        $existing = SiteText::query()->where('key', $key)->first();
        if ($ar === null && $en === null) {
            $existing?->delete();

            return;
        }

        ($existing ?? new SiteText(['key' => $key]))->fill(['value_ar' => $ar, 'value_en' => $en])->save();
    }
}

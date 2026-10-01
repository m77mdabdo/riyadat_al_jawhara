<?php

namespace App\Support;

use App\Models\SiteImage;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Resolves static image slots to URLs: an image uploaded in the admin
 * (Website Content → Site images) first, else the chosen stock file in
 * public/images/site (config/site-images.php). A slot only resolves when one
 * of them actually exists, otherwise the caller's fallback is used -- so a
 * missing file can never produce a broken image.
 */
class SiteImages
{
    public const DIRECTORY = 'images/site';

    public const CACHE_KEY = 'site-images.uploads';

    /** Smaller renditions ({slot}-{width}.webp) for srcset; see site-images:variants. */
    public const VARIANT_WIDTHS = [640, 1024];

    public static function path(string $slot): string
    {
        return public_path(self::DIRECTORY.'/'.$slot.'.webp');
    }

    /** The built-in (stock) file of the slot exists. */
    public static function hasFile(string $slot): bool
    {
        return array_key_exists($slot, config('site-images.slots', [])) && is_file(self::path($slot));
    }

    /** An uploaded or built-in image exists for the slot. */
    public static function exists(string $slot): bool
    {
        return self::upload($slot) !== null || self::hasFile($slot);
    }

    public static function url(string $slot, ?string $fallback = null): ?string
    {
        return self::upload($slot)['url'] ?? self::builtInUrl($slot) ?? $fallback;
    }

    /** URL of the built-in stock file, ignoring uploads. */
    public static function builtInUrl(string $slot): ?string
    {
        return self::hasFile($slot) ? asset(self::DIRECTORY.'/'.$slot.'.webp').'?v='.filemtime(self::path($slot)) : null;
    }

    /** Alt text entered for the uploaded image in the current language, or null. */
    public static function alt(string $slot): ?string
    {
        $upload = self::upload($slot);
        if (! $upload) {
            return null;
        }
        $locale = app()->getLocale();

        return $upload['alt_'.$locale] ?: ($upload['alt_'.($locale === 'ar' ? 'en' : 'ar')] ?: null);
    }

    /**
     * @return array{url: string, srcset: string|null, width: int, height: int, alt_ar: string|null, alt_en: string|null}|null
     */
    public static function upload(string $slot): ?array
    {
        return app(self::CACHE_KEY)[$slot] ?? null;
    }

    /**
     * Uploaded slot images, from the cache. Never throws (tables not migrated
     * yet, database down on error pages): built-in files are used then.
     *
     * @return array<string, array{url: string, srcset: string|null, width: int, height: int, alt_ar: string|null, alt_en: string|null}>
     */
    public static function loadUploads(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function () {
                $uploads = [];
                foreach (SiteImage::query()->with('media')->get() as $image) {
                    $media = $image->getFirstMedia('image');
                    if (! $media) {
                        continue;
                    }
                    $original = SiteImage::SLOTS[$image->slot]['original'] ?? false;
                    $candidates = [];
                    foreach (config('site.webp_widths') as $name => $width) {
                        if (! $original && $media->hasGeneratedConversion($name)) {
                            $candidates[$width] = $media->getUrl($name);
                        }
                    }
                    $url = $candidates !== [] ? end($candidates) : $media->getUrl();
                    $dimensions = rescue(fn () => getimagesize($candidates !== [] ? $media->getPath(array_search(max(array_keys($candidates)), config('site.webp_widths'), true)) : $media->getPath()), false, false);

                    $uploads[$image->slot] = [
                        'url' => $url,
                        'srcset' => count($candidates) > 1 ? collect($candidates)->map(fn ($u, $w) => "{$u} {$w}w")->implode(', ') : null,
                        'width' => $dimensions ? (int) $dimensions[0] : 1920,
                        'height' => $dimensions ? (int) $dimensions[1] : 1280,
                        'alt_ar' => $image->alt_ar,
                        'alt_en' => $image->alt_en,
                    ];
                }

                return $uploads;
            });
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

    public static function variantPath(string $slot, int $width): string
    {
        return public_path(self::DIRECTORY."/{$slot}-{$width}.webp");
    }

    /**
     * srcset of the existing smaller renditions plus the original, or null
     * when the slot has no file (callers then use their fallback src only).
     */
    public static function srcset(string $slot): ?string
    {
        if ($upload = self::upload($slot)) {
            return $upload['srcset'] ?? $upload['url'];
        }

        if (! self::hasFile($slot)) {
            return null;
        }

        $version = filemtime(self::path($slot));
        $original = self::fileSize($slot)[0];
        $candidates = [];

        foreach (self::VARIANT_WIDTHS as $width) {
            if ($width < $original && is_file(self::variantPath($slot, $width))) {
                $candidates[] = asset(self::DIRECTORY."/{$slot}-{$width}.webp")."?v={$version} {$width}w";
            }
        }
        $candidates[] = self::builtInUrl($slot)." {$original}w";

        return implode(', ', $candidates);
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    public static function size(string $slot): ?array
    {
        if ($upload = self::upload($slot)) {
            return [$upload['width'], $upload['height']];
        }

        return self::fileSize($slot);
    }

    /**
     * Size of the built-in stock file.
     *
     * @return array{0: int, 1: int}|null
     */
    public static function fileSize(string $slot): ?array
    {
        if (! self::hasFile($slot)) {
            return null;
        }

        [$width, $height] = getimagesize(self::path($slot));

        return [$width, $height];
    }

    /**
     * @return array<string, array<string, mixed>> slot => chosen candidate metadata
     */
    public static function choices(): array
    {
        $file = config('site-images.state_path');

        return is_file($file) ? json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR) : [];
    }
}

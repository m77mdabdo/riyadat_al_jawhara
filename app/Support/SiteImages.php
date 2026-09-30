<?php

namespace App\Support;

/**
 * Resolves static image slots (config/site-images.php) to URLs. A slot only
 * resolves when its chosen file actually exists, otherwise the caller's
 * fallback is used -- so a missing download can never produce a broken image.
 */
class SiteImages
{
    public const DIRECTORY = 'images/site';

    /** Smaller renditions ({slot}-{width}.webp) for srcset; see site-images:variants. */
    public const VARIANT_WIDTHS = [640, 1024];

    public static function path(string $slot): string
    {
        return public_path(self::DIRECTORY.'/'.$slot.'.webp');
    }

    public static function exists(string $slot): bool
    {
        return array_key_exists($slot, config('site-images.slots', [])) && is_file(self::path($slot));
    }

    public static function url(string $slot, ?string $fallback = null): ?string
    {
        return self::exists($slot) ? asset(self::DIRECTORY.'/'.$slot.'.webp').'?v='.filemtime(self::path($slot)) : $fallback;
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
        if (! self::exists($slot)) {
            return null;
        }

        $version = filemtime(self::path($slot));
        $original = self::size($slot)[0];
        $candidates = [];

        foreach (self::VARIANT_WIDTHS as $width) {
            if ($width < $original && is_file(self::variantPath($slot, $width))) {
                $candidates[] = asset(self::DIRECTORY."/{$slot}-{$width}.webp")."?v={$version} {$width}w";
            }
        }
        $candidates[] = self::url($slot)." {$original}w";

        return implode(', ', $candidates);
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    public static function size(string $slot): ?array
    {
        if (! self::exists($slot)) {
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

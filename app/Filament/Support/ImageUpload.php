<?php

namespace App\Filament\Support;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

/**
 * The only way admin forms accept images: JPG, PNG or WebP (no SVG, which
 * could carry scripts and is served from the site's own origin) and a size
 * cap. Models enforce the same MIME list on their media collections.
 */
class ImageUpload
{
    public const MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    /** Upload cap in kilobytes (config/site.php). */
    public static function maxKb(): int
    {
        return (int) config('site.uploads.max_kb');
    }

    public static function make(string $name, ?string $collection = null): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make($name)
            ->collection($collection ?? $name)
            ->image()
            ->acceptedFileTypes(self::MIME_TYPES)
            ->maxSize(self::maxKb())
            ->helperText(__('JPG, PNG or WebP, up to :size MB.', ['size' => round(self::maxKb() / 1024)]));
    }
}

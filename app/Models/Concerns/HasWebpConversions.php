<?php

namespace App\Models\Concerns;

/**
 * Responsive WebP renditions used for srcset on the public site.
 *
 * These are *additional* conversions: the existing JPEG "thumb"/"medium"
 * conversions are kept unchanged (open-graph images and already-generated
 * media keep working). Media uploaded before these existed simply has no
 * WebP versions until `php artisan media-library:regenerate --only-missing`
 * is run; the views fall back to the JPEG/original in the meantime.
 */
trait HasWebpConversions
{
    public const WEBP_WIDTHS = ['card' => 800, 'large' => 1600];

    protected function registerWebpConversions(): void
    {
        foreach (self::WEBP_WIDTHS as $name => $width) {
            $this->addMediaConversion($name)
                ->width($width)
                ->format('webp')
                ->quality(80)
                ->nonQueued();
        }
    }
}

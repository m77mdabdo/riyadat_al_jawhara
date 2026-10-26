<?php

namespace App\Console\Commands;

use App\Support\SiteImages;
use Illuminate\Console\Command;
use Spatie\Image\Image;

/**
 * Writes the smaller WebP renditions ({slot}-640.webp, {slot}-1024.webp) used
 * in srcset, so phones don't download the full-width file. Run after picking
 * or replacing a site image (site-images:pick calls it for the picked slot).
 */
class SiteImagesVariants extends Command
{
    protected $signature = 'site-images:variants {slot? : Only this slot (default: every slot with a file)}';

    protected $description = 'Generate responsive WebP renditions of the static site images';

    public function handle(): int
    {
        $slots = $this->argument('slot') ? [$this->argument('slot')] : array_keys(config('site-images.slots', []));

        foreach ($slots as $slot) {
            if (! SiteImages::exists($slot)) {
                $this->line("{$slot}: no image, skipped");

                continue;
            }

            $width = SiteImages::size($slot)[0];
            foreach (SiteImages::VARIANT_WIDTHS as $target) {
                $path = SiteImages::variantPath($slot, $target);
                if ($target >= $width) {
                    @unlink($path); // original is already this small

                    continue;
                }
                Image::load(SiteImages::path($slot))->width($target)->format('webp')->quality(72)->save($path);
                $this->line(sprintf('%s: %dw (%d KB)', $slot, $target, round(filesize($path) / 1024)));
            }
        }

        return self::SUCCESS;
    }
}

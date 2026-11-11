<?php

namespace App\Models;

use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasWebpConversions;
use App\Models\Concerns\LogsAdminActivity;
use App\Support\SiteImages;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * An admin replacement for a static site image slot. Without an uploaded
 * file the slot keeps its built-in image (public/images).
 *
 * @property string $slot
 * @property string|null $alt_ar
 * @property string|null $alt_en
 */
class SiteImage extends Model implements HasMedia
{
    use HasWebpConversions, InteractsWithMedia, LogsAdminActivity;

    protected $fillable = ['slot', 'alt_ar', 'alt_en'];

    /**
     * Editable slots: recommended size, ratio, built-in file (relative to
     * public/) and whether the original file is served (logos keep PNG
     * transparency) instead of the WebP rendition.
     *
     * @var array<string, array{size: string, ratio: string|null, fallback: string|null, original?: bool}>
     */
    public const SLOTS = [
        'hero' => ['size' => '1920×1280', 'ratio' => '3:2', 'fallback' => 'images/hero-poster.webp'],
        'about' => ['size' => '1920×1080', 'ratio' => '16:9', 'fallback' => 'images/about-hero.webp'],
        'texture-marble' => ['size' => '1280×853', 'ratio' => '3:2', 'fallback' => null],
        'texture-granite' => ['size' => '1280×853', 'ratio' => '3:2', 'fallback' => 'images/textures/stone-texture.webp'],
        'texture-sandstone' => ['size' => '1280×853', 'ratio' => '3:2', 'fallback' => 'images/textures/marble-texture.webp'],
        'texture-quartz' => ['size' => '1280×853', 'ratio' => '3:2', 'fallback' => null],
        'heritage-jeddah' => ['size' => '1880×1253', 'ratio' => '3:2', 'fallback' => null],
        'heritage-diriyah' => ['size' => '1880×1253', 'ratio' => '3:2', 'fallback' => null],
        'heritage-alula' => ['size' => '1880×1253', 'ratio' => '3:2', 'fallback' => null],
        'heritage-najdi' => ['size' => '1880×1253', 'ratio' => '3:2', 'fallback' => null],
        'cinema-quarry' => ['size' => '1880×1253', 'ratio' => '3:2', 'fallback' => null],
        'cinema-craft' => ['size' => '1880×1253', 'ratio' => '3:2', 'fallback' => null],
        'cinema-space' => ['size' => '1880×1253', 'ratio' => '3:2', 'fallback' => null],
        'logo-mobile' => ['size' => '212×128', 'ratio' => null, 'fallback' => 'logo-monogram-header-black@2x.png', 'original' => true],
    ];

    protected static function booted(): void
    {
        static::saved(fn () => SiteImages::flush());
        static::deleted(fn () => SiteImages::flush());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile()->acceptsMimeTypes(ImageUpload::MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerWebpConversions();
    }

    public function label(): string
    {
        return __('admin.site_image_slots.'.$this->slot.'.label');
    }

    public function usedOn(): string
    {
        return __('admin.site_image_slots.'.$this->slot.'.used');
    }

    /** Image shown now: the upload, the built-in file, or null. */
    public function currentUrl(): ?string
    {
        $media = $this->getFirstMedia('image');
        if ($media) {
            return $media->hasGeneratedConversion('card') ? $media->getUrl('card') : $media->getUrl();
        }

        $builtIn = SiteImages::builtInUrl($this->slot);

        return $builtIn ?? (($fallback = self::SLOTS[$this->slot]['fallback'] ?? null) ? asset($fallback) : null);
    }
}

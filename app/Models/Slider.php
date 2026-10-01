<?php

namespace App\Models;

use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasLocalizedAttributes;
use App\Models\Concerns\LogsAdminActivity;
use App\Models\Contracts\HasPublicUrl;
use Database\Factories\SliderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read string $title
 * @property-read string|null $subtitle
 * @property-read string|null $button_text
 */
class Slider extends Model implements HasMedia, HasPublicUrl
{
    /** @use HasFactory<SliderFactory> */
    use HasActiveScope, HasFactory, HasLocalizedAttributes, InteractsWithMedia, LogsAdminActivity, SoftDeletes;

    /**
     * Bilingual fields (see HasLocalizedAttributes): field => value when both languages are empty.
     *
     * @var array<string, string|null>
     */
    protected array $localized = [
        'title' => '',
        'subtitle' => null,
        'button_text' => null,
    ];

    protected $fillable = [
        'title_ar',
        'title_en',
        'subtitle_ar',
        'subtitle_en',
        'button_text_ar',
        'button_text_en',
        'button_link',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile()->acceptsMimeTypes(ImageUpload::MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->nonQueued()->width(400)->height(300);
        $this->addMediaConversion('medium')->nonQueued()->width(1200)->height(700);
        $this->addMediaConversion('hero')->nonQueued()->fit(Fit::Max, 1920, 1080)->format('webp')->quality(80);
    }

    /**
     * Full-width hero background for this slide, or null when the slide has no
     * image (the hero then falls back to the default video/poster). Media
     * uploaded before the 'hero' conversion existed uses the original file.
     */
    public function heroImageUrl(): ?string
    {
        $media = $this->getFirstMedia('image');

        if (! $media) {
            return null;
        }

        return $media->hasGeneratedConversion('hero') ? $media->getUrl('hero') : $media->getUrl();
    }

    public function publicUrl(?string $locale = null): ?string
    {
        return $this->is_active ? route('home', ['locale' => $locale ?? app()->getLocale()]) : null;
    }
}

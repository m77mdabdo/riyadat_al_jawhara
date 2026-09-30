<?php

namespace App\Models;

use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsAdminActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Slider extends Model implements HasMedia
{
    use HasActiveScope, HasFactory, HasTranslations, InteractsWithMedia, LogsAdminActivity;

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
        $this->addMediaConversion('thumb')->width(400)->height(300)->nonQueued();
        $this->addMediaConversion('medium')->width(1200)->height(700)->nonQueued();
        $this->addMediaConversion('hero')->fit(Fit::Max, 1920, 1080)->format('webp')->quality(80)->nonQueued();
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

    public function getTitleAttribute(): string
    {
        return $this->translated('title') ?? '';
    }

    public function getSubtitleAttribute(): ?string
    {
        return $this->translated('subtitle');
    }

    public function getButtonTextAttribute(): ?string
    {
        return $this->translated('button_text');
    }
}

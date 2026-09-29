<?php

namespace App\Models;

use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasTranslations;
use App\Support\ServiceIcon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Service extends Model implements HasMedia
{
    use HasActiveScope, HasSlug, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'icon',
        'description_ar',
        'description_en',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name_en')
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(400)->height(300)->nonQueued();
        $this->addMediaConversion('medium')->width(1200)->height(700)->nonQueued();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * The icon component to render publicly. Falls back to the default icon
     * for empty or unknown values so a bad value can never break a page.
     */
    public function getSafeIconAttribute(): string
    {
        return ServiceIcon::resolve($this->icon);
    }

    public function getNameAttribute(): string
    {
        return $this->translated('name') ?? '';
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->translated('description');
    }
}

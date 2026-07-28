<?php

namespace App\Models;

use App\Models\Concerns\HasActiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class StoneType extends Model implements HasMedia
{
    use HasActiveScope, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'stone_category_id',
        'name_ar',
        'name_en',
        'slug',
        'origin_ar',
        'origin_en',
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
        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(400)->height(300);
        $this->addMediaConversion('medium')->width(1200)->height(700);
    }

    public function stoneCategory(): BelongsTo
    {
        return $this->belongsTo(StoneCategory::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function getOriginAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->origin_ar : $this->origin_en;
    }

    public function getDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }
}

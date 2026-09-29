<?php

namespace App\Models;

use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Project extends Model implements HasMedia
{
    use HasActiveScope, HasSlug, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'title_ar',
        'title_en',
        'slug',
        'description_ar',
        'description_en',
        'location_ar',
        'location_en',
        'client_name',
        'sector',
        'area_m2',
        'city_ar',
        'city_en',
        'service_id',
        'stone_type_id',
        'completed_at',
        'is_featured',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'completed_at' => 'date',
        'area_m2' => 'decimal:2',
    ];

    /**
     * @var array<int, string> Allowed sector values (labels live in lang files: site.projects.sectors.*)
     */
    public const SECTORS = ['residential', 'commercial', 'hospitality', 'government', 'industrial'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title_en')
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('before');
        $this->addMediaCollection('after');
        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(400)->height(300)->nonQueued();
        $this->addMediaConversion('medium')->width(1200)->height(700)->nonQueued();
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function stoneType(): BelongsTo
    {
        return $this->belongsTo(StoneType::class);
    }

    /**
     * Additional stones used in the project. `stone_type_id` stays the
     * primary stone; see allStoneTypes() for the combined list.
     */
    public function stoneTypes(): BelongsToMany
    {
        return $this->belongsToMany(StoneType::class);
    }

    /**
     * Projects that use the stone either as primary or additional material.
     */
    public function scopeUsingStone(Builder $query, int $stoneTypeId): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('stone_type_id', $stoneTypeId)
            ->orWhereHas('stoneTypes', fn (Builder $pivot) => $pivot->where('stone_types.id', $stoneTypeId)));
    }

    /**
     * Primary stone first, then the additional ones, active only, no duplicates.
     *
     * @return Collection<int, StoneType>
     */
    public function allStoneTypes(): Collection
    {
        return collect([$this->stoneType])
            ->merge($this->stoneTypes)
            ->filter(fn (?StoneType $stone) => $stone?->is_active)
            ->unique('id')
            ->values();
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function getTitleAttribute(): string
    {
        return $this->translated('title') ?? '';
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->translated('description');
    }

    public function getCityAttribute(): ?string
    {
        return $this->translated('city');
    }

    public function getLocationAttribute(): ?string
    {
        return $this->translated('location');
    }
}

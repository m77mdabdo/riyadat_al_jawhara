<?php

namespace App\Models;

use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasSeoOverrides;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\HasWebpConversions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

class StoneType extends Model implements HasMedia
{
    use HasActiveScope, HasCoverImage, HasFactory, HasSeoOverrides, HasSlug, HasTranslations, HasWebpConversions, InteractsWithMedia;

    protected $fillable = [
        'stone_category_id',
        'name_ar',
        'name_en',
        'slug',
        'origin_ar',
        'origin_en',
        'origin_type',
        'description_ar',
        'description_en',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const ORIGIN_LOCAL = 'local';

    public const ORIGIN_IMPORTED = 'imported';

    /**
     * @var array<int, string>
     */
    public const ORIGIN_TYPES = [self::ORIGIN_LOCAL, self::ORIGIN_IMPORTED];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name_en')
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->acceptsMimeTypes(ImageUpload::MIME_TYPES);
        $this->addMediaCollection('gallery')->acceptsMimeTypes(ImageUpload::MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerWebpConversions();
        $this->addMediaConversion('thumb')->width(400)->height(300)->nonQueued();
        $this->addMediaConversion('medium')->width(1200)->height(700)->nonQueued();
    }

    public function stoneCategory(): BelongsTo
    {
        return $this->belongsTo(StoneCategory::class);
    }

    /**
     * Projects where this is the primary stone (projects.stone_type_id).
     * Use Project::usingStone() to include projects that list it as an
     * additional material.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * All specification values. The per-type relations below are what the
     * admin form and views use; they share the same pivot table.
     */
    public function stoneAttributes(): BelongsToMany
    {
        return $this->belongsToMany(StoneAttribute::class)->orderBy('stone_attributes.order');
    }

    public function finishes(): BelongsToMany
    {
        return $this->stoneAttributes()->where('stone_attributes.type', StoneAttribute::FINISH);
    }

    public function colours(): BelongsToMany
    {
        return $this->stoneAttributes()->where('stone_attributes.type', StoneAttribute::COLOUR);
    }

    public function sizes(): BelongsToMany
    {
        return $this->stoneAttributes()->where('stone_attributes.type', StoneAttribute::SIZE);
    }

    public function applications(): BelongsToMany
    {
        return $this->stoneAttributes()->where('stone_attributes.type', StoneAttribute::APPLICATION);
    }

    /**
     * Active specification values grouped by type, for the detail page.
     *
     * @return Collection<string, Collection<int, StoneAttribute>>
     */
    public function specificationGroups(): Collection
    {
        return $this->stoneAttributes
            ->where('is_active', true)
            ->groupBy('type')
            ->sortBy(fn ($group, string $type) => array_search($type, array_keys(StoneAttribute::TYPES), true));
    }

    public function getNameAttribute(): string
    {
        return $this->translated('name') ?? '';
    }

    public function getOriginAttribute(): ?string
    {
        return $this->translated('origin');
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->translated('description');
    }
}

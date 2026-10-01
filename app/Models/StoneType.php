<?php

namespace App\Models;

use App\Enums\StoneAttributeType;
use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasLocalizedAttributes;
use App\Models\Concerns\HasSeoOverrides;
use App\Models\Concerns\HasWebpConversions;
use App\Models\Concerns\LogsAdminActivity;
use App\Models\Contracts\HasPublicUrl;
use Database\Factories\StoneTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property-read string $name
 * @property-read string|null $origin
 * @property-read string|null $description
 */
class StoneType extends Model implements HasMedia, HasPublicUrl
{
    /** @use HasFactory<StoneTypeFactory> */
    use HasActiveScope, HasCoverImage, HasFactory, HasLocalizedAttributes, HasSeoOverrides, HasSlug, HasWebpConversions, InteractsWithMedia, LogsAdminActivity, SoftDeletes;

    /**
     * Bilingual fields (see HasLocalizedAttributes): field => value when both languages are empty.
     *
     * @var array<string, string|null>
     */
    protected array $localized = [
        'name' => '',
        'origin' => null,
        'description' => null,
    ];

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
        'spec_sheet_enabled',
        'order',
    ];

    /** Column defaults, so a new model behaves like the migration. */
    protected $attributes = [
        'spec_sheet_enabled' => true,
    ];

    protected $casts = [
        'spec_sheet_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    // origin_type values: App\Enums\StoneOriginType.

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
        $this->addMediaConversion('thumb')->nonQueued()->width(400)->height(300);
        $this->addMediaConversion('medium')->nonQueued()->width(1200)->height(700);
    }

    /** @return BelongsTo<StoneCategory, $this> */
    public function stoneCategory(): BelongsTo
    {
        return $this->belongsTo(StoneCategory::class);
    }

    /**
     * Projects where this is the primary stone (projects.stone_type_id).
     * Use Project::usingStone() to include projects that list it as an
     * additional material.
     *
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /** @return HasMany<Lead, $this> */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * All specification values. The per-type relations below are what the
     * admin form and views use; they share the same pivot table.
     *
     * @return BelongsToMany<StoneAttribute, $this>
     */
    public function stoneAttributes(): BelongsToMany
    {
        return $this->belongsToMany(StoneAttribute::class)->orderBy('stone_attributes.order');
    }

    /** @return BelongsToMany<StoneAttribute, $this> */
    public function finishes(): BelongsToMany
    {
        return $this->stoneAttributes()->where('stone_attributes.type', StoneAttributeType::Finish->value);
    }

    /** @return BelongsToMany<StoneAttribute, $this> */
    public function colours(): BelongsToMany
    {
        return $this->stoneAttributes()->where('stone_attributes.type', StoneAttributeType::Colour->value);
    }

    /** @return BelongsToMany<StoneAttribute, $this> */
    public function sizes(): BelongsToMany
    {
        return $this->stoneAttributes()->where('stone_attributes.type', StoneAttributeType::Size->value);
    }

    /** @return BelongsToMany<StoneAttribute, $this> */
    public function applications(): BelongsToMany
    {
        return $this->stoneAttributes()->where('stone_attributes.type', StoneAttributeType::Application->value);
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
            ->toBase()
            ->groupBy('type')
            ->sortBy(fn ($group, string $type) => array_search($type, StoneAttributeType::values(), true));
    }

    public function publicUrl(?string $locale = null): ?string
    {
        return $this->is_active ? route('stones.show', ['locale' => $locale ?? app()->getLocale(), 'stoneType' => $this]) : null;
    }
}

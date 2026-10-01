<?php

namespace App\Models;

use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasLocalizedAttributes;
use App\Models\Concerns\HasWebpConversions;
use App\Models\Concerns\LogsAdminActivity;
use App\Models\Contracts\HasPublicUrl;
use Database\Factories\StoneCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property-read string|null $description
 * @property-read string $name
 */
class StoneCategory extends Model implements HasMedia, HasPublicUrl
{
    /** @use HasFactory<StoneCategoryFactory> */
    use HasCoverImage, HasFactory, HasLocalizedAttributes, HasSlug, HasWebpConversions, InteractsWithMedia, LogsAdminActivity, SoftDeletes;

    /**
     * Bilingual fields (see HasLocalizedAttributes): field => value when both languages are empty.
     *
     * @var array<string, string|null>
     */
    protected array $localized = [
        'description' => null,
        'name' => '',
    ];

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'slug',
        'order',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->acceptsMimeTypes(ImageUpload::MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerWebpConversions();
        $this->addMediaConversion('medium')->nonQueued()->width(1200)->height(700);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name_en')
            ->saveSlugsTo('slug');
    }

    /** @return HasMany<StoneType, $this> */
    public function stoneTypes(): HasMany
    {
        return $this->hasMany(StoneType::class);
    }

    public function publicUrl(?string $locale = null): ?string
    {
        return route('stones.index', ['locale' => $locale ?? app()->getLocale(), 'stone_category' => $this->slug]);
    }
}

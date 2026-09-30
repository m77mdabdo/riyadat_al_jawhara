<?php

namespace App\Models;

use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\LogsAdminActivity;
use Database\Factories\StoneAttributeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * A specification value a stone can have: a finish (polished, honed...),
 * a colour, a size/thickness, or an application (interior, flooring...).
 * Managed in the admin panel; used for stone detail specs and public filters.
 */
class StoneAttribute extends Model
{
    /** @use HasFactory<StoneAttributeFactory> */
    use HasActiveScope, HasFactory, HasSlug, HasTranslations, LogsAdminActivity;

    // Types: App\Enums\StoneAttributeType.

    protected $fillable = [
        'type',
        'name_ar',
        'name_en',
        'slug',
        'hex_color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        // Slugs only need to be unique within a type ("white" can be a colour
        // and never clash with anything else).
        return SlugOptions::create()
            ->generateSlugsFrom('name_en')
            ->saveSlugsTo('slug')
            ->extraScope(fn (Builder $builder) => $builder->where('type', $this->type));
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /** @return BelongsToMany<StoneType, $this> */
    public function stoneTypes(): BelongsToMany
    {
        return $this->belongsToMany(StoneType::class);
    }

    /**
     * The swatch colour, only if it is a strict #RRGGBB value (it is written
     * into a style attribute, so nothing else may pass through).
     */
    public function getSwatchAttribute(): ?string
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $this->hex_color) ? $this->hex_color : null;
    }

    public function getNameAttribute(): string
    {
        return $this->translated('name') ?? '';
    }
}

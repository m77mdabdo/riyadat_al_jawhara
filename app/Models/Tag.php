<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAttributes;
use App\Models\Concerns\LogsAdminActivity;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property-read string $name
 */
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory, HasLocalizedAttributes, HasSlug, LogsAdminActivity;

    /**
     * Bilingual fields (see HasLocalizedAttributes): field => value when both languages are empty.
     *
     * @var array<string, string|null>
     */
    protected array $localized = [
        'name' => '',
    ];

    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name_en')
            ->saveSlugsTo('slug');
    }

    /** @return BelongsToMany<Post, $this> */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}

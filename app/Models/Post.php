<?php

namespace App\Models;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasCoverImage;
use App\Models\Concerns\HasLocalizedAttributes;
use App\Models\Concerns\HasSeoOverrides;
use App\Models\Concerns\HasWebpConversions;
use App\Models\Concerns\LogsAdminActivity;
use App\Models\Contracts\HasPublicUrl;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property-read string $title
 * @property-read string|null $excerpt
 * @property-read string|null $body
 */
class Post extends Model implements HasMedia, HasPublicUrl
{
    /** @use HasFactory<PostFactory> */
    use HasCoverImage, HasFactory, HasLocalizedAttributes, HasSeoOverrides, HasSlug, HasWebpConversions, InteractsWithMedia, LogsAdminActivity, SoftDeletes;

    /**
     * Bilingual fields (see HasLocalizedAttributes): field => value when both languages are empty.
     *
     * @var array<string, string|null>
     */
    protected array $localized = [
        'title' => '',
        'excerpt' => null,
        'body' => null,
    ];

    protected $fillable = [
        'title_ar',
        'title_en',
        'slug',
        'excerpt_ar',
        'excerpt_en',
        'body_ar',
        'body_en',
        'is_published',
        'published_at',
        'author_id',
        'blog_category_id',
        'views_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'views_count' => 'integer',
    ];

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('published_at', '<=', now());
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title_en')
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->acceptsMimeTypes(ImageUpload::MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerWebpConversions();
        $this->addMediaConversion('thumb')->nonQueued()->width(400)->height(300);
        $this->addMediaConversion('medium')->nonQueued()->width(1200)->height(700);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsTo<BlogCategory, $this> */
    public function blogCategory(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /** @return HasMany<Comment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** @return HasMany<Comment, $this> */
    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('status', CommentStatus::Approved->value)->whereNull('parent_id');
    }

    public function publicUrl(?string $locale = null): ?string
    {
        return $this->is_published && $this->published_at?->isPast() ? route('blog.show', ['locale' => $locale ?? app()->getLocale(), 'post' => $this]) : null;
    }

    public function status(): PostStatus
    {
        return match (true) {
            ! $this->is_published || $this->published_at === null => PostStatus::Draft,
            $this->published_at->isFuture() => PostStatus::Scheduled,
            default => PostStatus::Published,
        };
    }

    /** Estimated reading time in minutes (about 200 words a minute). */
    public function readingMinutes(string $locale): int
    {
        $text = trim(strip_tags(html_entity_decode((string) $this->getAttribute("body_{$locale}"))));
        $words = $text === '' ? 0 : count(preg_split('/\s+/u', $text) ?: []);

        return max(1, (int) ceil($words / 200));
    }
}

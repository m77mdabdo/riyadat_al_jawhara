<?php

namespace App\Models;

use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasLocalizedAttributes;
use App\Models\Concerns\HasWebpConversions;
use App\Models\Concerns\LogsAdminActivity;
use App\Models\Contracts\HasPublicUrl;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read string $comment
 */
class Testimonial extends Model implements HasMedia, HasPublicUrl
{
    /** @use HasFactory<TestimonialFactory> */
    use HasActiveScope, HasFactory, HasLocalizedAttributes, HasWebpConversions, InteractsWithMedia, LogsAdminActivity, SoftDeletes;

    /**
     * Bilingual fields (see HasLocalizedAttributes): field => value when both languages are empty.
     *
     * @var array<string, string|null>
     */
    protected array $localized = [
        'comment' => '',
    ];

    protected $fillable = [
        'client_name',
        'rating',
        'comment_ar',
        'comment_en',
        'project_id',
        'is_active',
        'order',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_active' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('client_photo')->singleFile()->acceptsMimeTypes(ImageUpload::MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerWebpConversions();
        $this->addMediaConversion('thumb')->nonQueued()->width(200)->height(200);
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function publicUrl(?string $locale = null): ?string
    {
        return $this->is_active ? route('home', ['locale' => $locale ?? app()->getLocale()]) : null;
    }
}

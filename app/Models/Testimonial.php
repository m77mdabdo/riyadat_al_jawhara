<?php

namespace App\Models;

use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\HasWebpConversions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Testimonial extends Model implements HasMedia
{
    use HasActiveScope, HasTranslations, HasWebpConversions, InteractsWithMedia;

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
        $this->addMediaConversion('thumb')->width(200)->height(200)->nonQueued();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getCommentAttribute(): string
    {
        return $this->translated('comment') ?? '';
    }
}

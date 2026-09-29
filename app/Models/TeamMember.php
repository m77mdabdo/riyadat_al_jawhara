<?php

namespace App\Models;

use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\HasWebpConversions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TeamMember extends Model implements HasMedia
{
    use HasActiveScope, HasFactory, HasTranslations, HasWebpConversions, InteractsWithMedia;

    protected $fillable = [
        'name_ar',
        'name_en',
        'position_ar',
        'position_en',
        'phone',
        'email',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile()->acceptsMimeTypes(ImageUpload::MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerWebpConversions();
        $this->addMediaConversion('thumb')->width(300)->height(300)->nonQueued();
    }

    public function getNameAttribute(): string
    {
        return $this->translated('name') ?? '';
    }

    public function getPositionAttribute(): ?string
    {
        return $this->translated('position');
    }
}

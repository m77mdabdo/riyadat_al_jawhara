<?php

namespace App\Models;

use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasActiveScope;
use App\Models\Concerns\HasLocalizedAttributes;
use App\Models\Concerns\HasWebpConversions;
use App\Models\Concerns\LogsAdminActivity;
use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read string $name
 * @property-read string|null $position
 */
class TeamMember extends Model implements HasMedia
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasActiveScope, HasFactory, HasLocalizedAttributes, HasWebpConversions, InteractsWithMedia, LogsAdminActivity;

    /**
     * Bilingual fields (see HasLocalizedAttributes): field => value when both languages are empty.
     *
     * @var array<string, string|null>
     */
    protected array $localized = [
        'name' => '',
        'position' => null,
    ];

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
        $this->addMediaConversion('thumb')->nonQueued()->width(300)->height(300);
    }
}

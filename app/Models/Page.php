<?php

namespace App\Models;

use App\Enums\PageKey;
use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasLocalizedAttributes;
use App\Models\Concerns\HasSeoOverrides;
use App\Models\Concerns\HasWebpConversions;
use App\Models\Concerns\LogsAdminActivity;
use App\Models\Contracts\HasPublicUrl;
use App\Support\PageContent;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Editable parts of a fixed public page: banner, extra content blocks,
 * options (contact form fields) and SEO. Header texts are site texts
 * (PageKey::titleKey()/subtitleKey()).
 *
 * @property PageKey $key
 * @property array<array-key, array<string, mixed>>|null $blocks
 * @property array<string, mixed>|null $options
 * @property bool $noindex
 */
class Page extends Model implements HasMedia, HasPublicUrl
{
    use HasLocalizedAttributes, HasSeoOverrides, HasWebpConversions, InteractsWithMedia, LogsAdminActivity;

    /** @var array<string, string|null> */
    protected array $localized = ['meta_title' => null, 'meta_description' => null];

    protected $fillable = ['key', 'blocks', 'options', 'noindex'];

    protected $casts = [
        'key' => PageKey::class,
        'blocks' => 'array',
        'options' => 'array',
        'noindex' => 'boolean',
    ];

    protected $attributes = ['noindex' => false];

    protected static function booted(): void
    {
        static::saved(fn () => PageContent::flush());
        static::deleted(fn () => PageContent::flush());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')->singleFile()->acceptsMimeTypes(ImageUpload::MIME_TYPES);
        $this->addMediaCollection('og_image')->singleFile()->acceptsMimeTypes(ImageUpload::MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerWebpConversions();
        $this->addMediaConversion('og')->nonQueued()->performOnCollections('og_image')->fit(Fit::Crop, 1200, 630);
    }

    public function option(string $key, mixed $default = null): mixed
    {
        $value = data_get($this->options, $key);

        return $value === null || $value === '' ? $default : $value;
    }

    public function publicUrl(?string $locale = null): ?string
    {
        $route = $this->key->route();

        return $route ? route($route, ['locale' => $locale ?? app()->getLocale()]) : null;
    }
}

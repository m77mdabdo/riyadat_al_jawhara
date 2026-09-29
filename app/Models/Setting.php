<?php

namespace App\Models;

use App\Filament\Support\ImageUpload;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Setting extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'phone',
        'whatsapp',
        'email',
        'address_ar',
        'address_en',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'snapchat_url',
        'linkedin_url',
        'x_url',
        'youtube_url',
        'working_hours_ar',
        'working_hours_en',
        'about_ar',
        'about_en',
        'vision_ar',
        'vision_en',
        'mission_ar',
        'mission_en',
        'map_lat',
        'map_lng',
        'privacy_policy_ar',
        'privacy_policy_en',
        'terms_ar',
        'terms_en',
    ];

    /**
     * Static fallbacks shipped in public/ -- used until an admin uploads a
     * replacement in Settings → Branding.
     */
    public const DEFAULT_LOGO = 'logo-header-black@2x.png';

    public const DEFAULT_LOGO_LIGHT = 'logo-header-white@2x.png';

    public const DEFAULT_OG_IMAGE = 'og-image-1200x630.png';

    public function registerMediaCollections(): void
    {
        $mimes = ImageUpload::MIME_TYPES;

        // Logos keep their original file (no conversion) so PNG transparency survives.
        $this->addMediaCollection('logo')->singleFile()->acceptsMimeTypes($mimes);
        $this->addMediaCollection('logo_light')->singleFile()->acceptsMimeTypes($mimes);
        $this->addMediaCollection('og_image')->singleFile()->acceptsMimeTypes($mimes);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('og')
            ->performOnCollections('og_image')
            ->fit(Fit::Crop, 1200, 630)
            ->nonQueued();
    }

    public function logoUrl(): string
    {
        return $this->getFirstMediaUrl('logo') ?: asset(self::DEFAULT_LOGO);
    }

    public function logoLightUrl(): string
    {
        return $this->getFirstMediaUrl('logo_light') ?: asset(self::DEFAULT_LOGO_LIGHT);
    }

    public function ogImageUrl(): string
    {
        $media = $this->getFirstMedia('og_image');

        if ($media) {
            return $media->hasGeneratedConversion('og') ? $media->getUrl('og') : $media->getUrl();
        }

        return asset(self::DEFAULT_OG_IMAGE);
    }

    /**
     * Configured social profile URLs, in display order.
     *
     * @return array<string, string> network => url
     */
    public function socialLinks(): array
    {
        return array_filter([
            'facebook' => $this->facebook_url,
            'instagram' => $this->instagram_url,
            'x' => $this->x_url,
            'linkedin' => $this->linkedin_url,
            'youtube' => $this->youtube_url,
            'tiktok' => $this->tiktok_url,
            'snapchat' => $this->snapchat_url,
        ]);
    }

    public const CACHE_KEY = 'settings.current';

    protected static function booted(): void
    {
        // Any change to the settings row invalidates the cache and this
        // request's memoized copy.
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /**
     * The site settings for reading. Memoized per request (scoped container
     * instance) and cached across requests, so layouts, partials and
     * controllers can all call it for one query at most -- usually none.
     *
     * Never writes: on a fresh database it returns an unsaved, empty
     * instance instead of inserting a row during a GET request.
     */
    public static function current(): self
    {
        return app(self::CACHE_KEY);
    }

    /**
     * Loads the row for the scoped container binding (see AppServiceProvider).
     */
    public static function loadCurrent(): self
    {
        $attributes = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->first()?->getAttributes());

        return $attributes ? (new static)->newFromBuilder($attributes) : new static;
    }

    /**
     * The settings row for writing (admin Settings page, seeders, tests):
     * creates it if it doesn't exist yet.
     */
    public static function editable(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        app()->forgetInstance(self::CACHE_KEY);
    }

    /**
     * WhatsApp number as wa.me expects it: digits only, international format
     * without "+" (admins may type "+966 55 ..."). Null when not usable.
     */
    public function getWhatsappNumberAttribute(): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $this->whatsapp);

        return $digits !== '' ? $digits : null;
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        return $this->whatsapp_number ? 'https://wa.me/'.$this->whatsapp_number : null;
    }

    /**
     * Phone as a tel: URI -- digits with an optional leading "+".
     */
    public function getPhoneHrefAttribute(): ?string
    {
        $phone = trim((string) $this->phone);
        $digits = preg_replace('/\D/', '', $phone);

        if ($digits === '') {
            return null;
        }

        return 'tel:'.(str_starts_with($phone, '+') ? '+' : '').$digits;
    }

    public function getAddressAttribute(): ?string
    {
        return $this->translated('address');
    }

    public function getWorkingHoursAttribute(): ?string
    {
        return $this->translated('working_hours');
    }

    public function getAboutAttribute(): ?string
    {
        return $this->translated('about');
    }

    public function getVisionAttribute(): ?string
    {
        return $this->translated('vision');
    }

    public function getMissionAttribute(): ?string
    {
        return $this->translated('mission');
    }

    public function getPrivacyPolicyAttribute(): ?string
    {
        return $this->translated('privacy_policy');
    }

    public function getTermsAttribute(): ?string
    {
        return $this->translated('terms');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
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
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function getAddressAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->address_ar : $this->address_en;
    }

    public function getWorkingHoursAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->working_hours_ar : $this->working_hours_en;
    }

    public function getAboutAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->about_ar : $this->about_en;
    }

    public function getVisionAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->vision_ar : $this->vision_en;
    }

    public function getMissionAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->mission_ar : $this->mission_en;
    }
}

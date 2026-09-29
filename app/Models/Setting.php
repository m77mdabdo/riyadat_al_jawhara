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
        'privacy_policy_ar',
        'privacy_policy_en',
        'terms_ar',
        'terms_en',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
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

    public function getPrivacyPolicyAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->privacy_policy_ar : $this->privacy_policy_en;
    }

    public function getTermsAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->terms_ar : $this->terms_en;
    }
}

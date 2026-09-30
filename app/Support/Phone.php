<?php

namespace App\Support;

/**
 * Phone number helpers for links in emails and the admin.
 */
final class Phone
{
    /**
     * International digits for wa.me links. Saudi local formats (05xxxxxxxx,
     * 5xxxxxxxx) get the 966 country code; 00-prefixed numbers lose the 00.
     * Returns null when the number is too short to be real.
     */
    public static function whatsappDigits(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        $digits = match (true) {
            str_starts_with($digits, '00') => substr($digits, 2),
            str_starts_with($digits, '05') && strlen($digits) === 10 => '966'.substr($digits, 1),
            str_starts_with($digits, '5') && strlen($digits) === 9 => '966'.$digits,
            default => $digits,
        };

        return strlen($digits) >= 8 ? $digits : null;
    }

    /** tel: URI (digits with an optional leading +). */
    public static function telHref(?string $phone): ?string
    {
        $clean = preg_replace('/[^\d+]/', '', (string) $phone);

        return $clean !== '' ? 'tel:'.$clean : null;
    }
}

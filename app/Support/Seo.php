<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Site-wide SEO options (Settings → SEO).
 */
class Seo
{
    public const DEFAULT_TITLE_PATTERN = '{page} | {site}';

    /** Search-console verification tokens: letters, digits, "-" and "_". */
    public const VERIFICATION_PATTERN = '/^[A-Za-z0-9_\-]{10,100}$/';

    public static function title(string $page, string $site): string
    {
        $pattern = Setting::current()->option('seo_options', 'title_pattern', self::DEFAULT_TITLE_PATTERN);
        if (! is_string($pattern) || ! str_contains($pattern, '{page}')) {
            $pattern = self::DEFAULT_TITLE_PATTERN;
        }

        return trim(strtr($pattern, ['{page}' => $page, '{site}' => $site]));
    }

    public static function noindexSite(): bool
    {
        return (bool) Setting::current()->option('seo_options', 'noindex', false);
    }

    /**
     * @return array<string, string> meta name => verification code
     */
    public static function verifications(): array
    {
        $codes = [];
        foreach (['google-site-verification' => 'google_verification', 'msvalidate.01' => 'bing_verification'] as $meta => $key) {
            $code = Setting::current()->option('seo_options', $key);
            if (is_string($code) && preg_match(self::VERIFICATION_PATTERN, $code)) {
                $codes[$meta] = $code;
            }
        }

        return $codes;
    }
}

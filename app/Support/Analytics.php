<?php

namespace App\Support;

/**
 * Reads config/analytics.php and derives what the page and the CSP need.
 */
class Analytics
{
    public static function ga4(): ?string
    {
        $id = config('analytics.ga4_id');

        return is_string($id) && preg_match('/^G-[A-Z0-9]{4,20}$/', $id) ? $id : null;
    }

    public static function plausible(): ?array
    {
        $domain = config('analytics.plausible_domain');
        $src = config('analytics.plausible_src');

        if (! is_string($domain) || $domain === '' || ! is_string($src) || ! str_starts_with($src, 'https://')) {
            return null;
        }

        return ['domain' => $domain, 'src' => $src];
    }

    public static function enabled(): bool
    {
        return self::ga4() !== null || self::plausible() !== null;
    }

    /**
     * Client config for resources/js/analytics.js (null when disabled).
     */
    public static function clientConfig(): ?array
    {
        if (! self::enabled()) {
            return null;
        }

        return array_filter(['ga4' => self::ga4(), 'plausible' => self::plausible()]);
    }

    /**
     * Extra CSP sources, only for the configured provider(s).
     *
     * @return array{script: array<int, string>, connect: array<int, string>}
     */
    public static function cspSources(): array
    {
        $script = $connect = [];

        if (self::ga4()) {
            $script[] = 'https://www.googletagmanager.com';
            $connect = array_merge($connect, ['https://www.google-analytics.com', 'https://*.google-analytics.com', 'https://*.analytics.google.com', 'https://www.googletagmanager.com']);
        }

        if ($plausible = self::plausible()) {
            $origin = parse_url($plausible['src'], PHP_URL_SCHEME).'://'.parse_url($plausible['src'], PHP_URL_HOST);
            $script[] = $origin;
            $connect[] = $origin;
        }

        return ['script' => array_values(array_unique($script)), 'connect' => array_values(array_unique($connect))];
    }
}

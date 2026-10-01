<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Analytics provider from Settings → Analytics, else config/analytics.php
 * (.env), and what the page and the CSP need for it.
 */
class Analytics
{
    /** Format examples shown in the admin. */
    public const GA4_EXAMPLE = 'G-XXXXXXXXXX';

    public const DOMAIN_EXAMPLE = 'example.sa';

    public const GA4_PATTERN = '/^G-[A-Z0-9]{4,20}$/';

    public const DOMAIN_PATTERN = '/^(?=.{3,120}$)([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,}$/i';

    /**
     * Admin value for a key, or the configured one. Never throws (error pages).
     */
    private static function value(string $key): mixed
    {
        $options = rescue(fn () => Setting::current()->analytics_options, [], false) ?? [];
        if (! empty($options['disabled'])) {
            return null;
        }

        return filled($options[$key] ?? null) ? $options[$key] : config('analytics.'.$key);
    }

    public static function ga4(): ?string
    {
        $id = self::value('ga4_id');

        return is_string($id) && preg_match(self::GA4_PATTERN, $id) ? $id : null;
    }

    /**
     * @return array{domain: string, src: string}|null
     */
    public static function plausible(): ?array
    {
        $domain = self::value('plausible_domain');
        $src = config('analytics.plausible_src');

        if (! is_string($domain) || ! preg_match(self::DOMAIN_PATTERN, $domain) || ! is_string($src) || ! str_starts_with($src, 'https://')) {
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
     *
     * @return array<string, mixed>|null
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

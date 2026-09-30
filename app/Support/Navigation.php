<?php

namespace App\Support;

/**
 * The public site's main navigation, shared by the header and the footer.
 */
final class Navigation
{
    /** Route names in menu order; labels come from site.nav.{key}. */
    private const ITEMS = [
        'home' => 'home',
        'services.index' => 'services',
        'stones.index' => 'stones',
        'projects.index' => 'projects',
        'gallery' => 'gallery',
        'blog.index' => 'blog',
        'faq' => 'faq',
        'about' => 'about',
        'contact' => 'contact',
    ];

    /**
     * @return list<array{route: string, url: string, label: string, active: bool}>
     */
    public static function main(): array
    {
        $links = [];
        foreach (self::ITEMS as $route => $key) {
            $links[] = [
                'route' => $route,
                'url' => route($route),
                'label' => __("site.nav.{$key}"),
                'active' => self::isActive($route),
            ];
        }

        return $links;
    }

    /** "services.index" is also active on "services.show", etc. */
    private static function isActive(string $route): bool
    {
        $base = str_ends_with($route, '.index') ? substr($route, 0, -6) : $route;

        return request()->routeIs($route, $base.'.*');
    }
}

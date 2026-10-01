<?php

namespace App\Support;

use App\Enums\MenuLinkType;
use App\Enums\MenuLocation;
use App\Models\BlogCategory;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\StoneCategory;
use App\Models\StoneType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

/**
 * The public site's menus: header, footer columns and mobile menu, edited in
 * Website Content → Menus. A menu without active items uses the built-in
 * navigation below, so an empty database shows the site exactly as shipped.
 */
final class Navigation
{
    /** Per-request copy of the resolved menus, per language. */
    public const MEMO = 'navigation.menus.';

    public const VERSION_KEY = 'navigation.menus:version';

    /** Models a menu item can link to (Record links). */
    public const LINKABLE = [
        Service::class, StoneType::class, StoneCategory::class,
        Project::class, Post::class, BlogCategory::class,
    ];

    /** Built-in menu: route names in order; labels come from site.nav.{key}. */
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

    /** Pages a menu item may point to, besides the built-in menu ones. */
    private const EXTRA_ROUTES = [
        'search' => 'site.search.title',
        'legal.privacy' => 'site.legal.privacy_policy_title',
        'legal.terms' => 'site.legal.terms_title',
    ];

    /**
     * Pages a menu item can link to: route name => translated page name.
     *
     * @return array<string, string>
     */
    public static function routes(): array
    {
        $routes = [];
        foreach (self::ITEMS as $route => $key) {
            $routes[$route] = __("site.nav.{$key}");
        }
        foreach (self::EXTRA_ROUTES as $route => $key) {
            $routes[$route] = __($key);
        }

        return $routes;
    }

    /**
     * Header links.
     *
     * @return list<array{route: ?string, url: string, label: string, active: bool, new_tab: bool, children: list<array<string, mixed>>}>
     */
    public static function main(): array
    {
        return self::withActive(self::menu(MenuLocation::Header) ?? self::builtIn());
    }

    /**
     * Phone menu: its own items, else the header's.
     *
     * @return list<array{route: ?string, url: string, label: string, active: bool, new_tab: bool, children: list<array<string, mixed>>}>
     */
    public static function mobile(): array
    {
        $items = self::menu(MenuLocation::Mobile);

        return $items !== null ? self::withActive($items) : self::main();
    }

    /**
     * Footer columns: items with sub-items become their own column; the
     * remaining items (or the built-in menu without home) form "Quick links".
     *
     * @return list<array{title: string, links: list<array<string, mixed>>}>
     */
    public static function footerColumns(): array
    {
        $items = self::menu(MenuLocation::Footer);
        if ($items === null) {
            $links = array_values(array_filter(self::builtIn(), fn (array $link) => $link['route'] !== 'home'));

            return [['title' => __('site.footer.quick_links'), 'links' => $links]];
        }

        $columns = [];
        $loose = [];
        foreach ($items as $item) {
            if ($item['children'] !== []) {
                $columns[] = ['title' => $item['label'], 'links' => $item['children']];
            } else {
                $loose[] = $item;
            }
        }
        if ($loose !== []) {
            array_unshift($columns, ['title' => __('site.footer.quick_links'), 'links' => $loose]);
        }

        return $columns;
    }

    /**
     * Header options: call-to-action button, language switch, search.
     *
     * @return array{cta: ?array{label: string, url: string}, language_switch: bool, search: bool}
     */
    public static function header(): array
    {
        $options = self::all()['options'][MenuLocation::Header->value] ?? [];
        $locale = app()->getLocale();
        $ctaUrl = filled($options['cta_link'] ?? null) ? LocalizedUrl::link($options['cta_link']) : route('contact');

        return [
            'cta' => ($options['cta_show'] ?? true) ? [
                'label' => ($options['cta_label_'.$locale] ?? null) ?: __('site.common.request_quote'),
                'url' => $ctaUrl,
            ] : null,
            'language_switch' => (bool) ($options['show_language_switch'] ?? true),
            'search' => (bool) ($options['show_search'] ?? true),
        ];
    }

    /**
     * @return list<array{route: ?string, url: string, label: string, active: bool, new_tab: bool, children: list<array<string, mixed>>}>
     */
    private static function builtIn(): array
    {
        $links = [];
        foreach (self::ITEMS as $route => $key) {
            $links[] = ['route' => $route, 'url' => route($route), 'label' => __("site.nav.{$key}"), 'active' => false, 'new_tab' => false, 'icon' => null, 'children' => []];
        }

        return $links;
    }

    /**
     * Active items of a menu, or null when it has none (= built-in fallback).
     *
     * @return list<array<string, mixed>>|null
     */
    private static function menu(MenuLocation $location): ?array
    {
        $items = self::all()['items'][$location->value] ?? [];

        return $items !== [] ? $items : null;
    }

    /**
     * Every menu resolved for the current language, cached until flush().
     *
     * @return array{items: array<string, list<array<string, mixed>>>, options: array<string, array<string, mixed>>}
     */
    private static function all(): array
    {
        $memo = self::MEMO.app()->getLocale();
        if (app()->bound($memo)) {
            return app($memo);
        }

        try {
            $version = Cache::rememberForever(self::VERSION_KEY, fn () => Str::random(12));
            $scope = request()->getSchemeAndHttpHost().':'.app()->getLocale();
            $menus = Cache::rememberForever("navigation.menus:{$version}:{$scope}", fn () => self::build());
        } catch (Throwable) {
            $menus = ['items' => [], 'options' => []];
        }
        app()->instance($memo, $menus);

        return $menus;
    }

    /**
     * Rebuild the menus on the next request: after a menu change, or when a
     * record a menu item links to changes (its address or visibility may have).
     */
    public static function flush(): void
    {
        Cache::forget(self::VERSION_KEY);
        foreach (config('locales.supported') as $locale) {
            app()->forgetInstance(self::MEMO.$locale);
        }
    }

    public static function flushIfLinked(Model $record): void
    {
        if (! $record->wasRecentlyCreated && rescue(fn () => MenuItem::query()
            ->where('linkable_type', $record->getMorphClass())->where('linkable_id', $record->getKey())->exists(), false, false)) {
            self::flush();
        }
    }

    /**
     * @return array{items: array<string, list<array<string, mixed>>>, options: array<string, array<string, mixed>>}
     */
    private static function build(): array
    {
        try {
            $menus = Menu::query()->with(['items.linkable', 'items.children.linkable'])->get();
        } catch (Throwable) {
            return ['items' => [], 'options' => []];
        }

        $result = ['items' => [], 'options' => []];
        foreach ($menus as $menu) {
            $result['options'][$menu->location->value] = $menu->options ?? [];
            $result['items'][$menu->location->value] = self::resolve($menu->items);
        }

        return $result;
    }

    /**
     * @param  iterable<MenuItem>  $items
     * @return list<array<string, mixed>>
     */
    private static function resolve(iterable $items): array
    {
        $links = [];
        foreach ($items as $item) {
            if (! $item->is_active) {
                continue;
            }
            $children = $item->relationLoaded('children') ? self::resolve($item->children) : [];
            $url = $item->href();
            // A parent without its own target still groups its sub-items.
            if ($url === null && $children === []) {
                continue;
            }
            $links[] = [
                'route' => $item->link_type === MenuLinkType::Route ? $item->route_name : null,
                'url' => $url ?? '#',
                'label' => (string) $item->label,
                'active' => false,
                'new_tab' => $item->open_in_new_tab,
                'icon' => in_array($item->icon, MenuItem::ICONS, true) ? $item->icon : null,
                'children' => $children,
            ];
        }

        return $links;
    }

    /**
     * @param  list<array<string, mixed>>  $links
     * @return list<array<string, mixed>>
     */
    private static function withActive(array $links): array
    {
        $current = url()->current();
        foreach ($links as &$link) {
            $link['children'] = self::withActive($link['children']);
            $link['active'] = ($link['route'] ? self::isActive($link['route']) : $link['url'] === $current)
                || in_array(true, array_column($link['children'], 'active'), true);
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

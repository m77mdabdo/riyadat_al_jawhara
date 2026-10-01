<?php

namespace App\Actions\SiteContent;

use App\Enums\MenuLinkType;
use App\Enums\MenuLocation;
use App\Enums\PageKey;
use App\Models\Menu;
use App\Models\Page;
use App\Models\SiteImage;
use Illuminate\Support\Facades\DB;

/**
 * Creates the editable website-content rows that don't exist yet, filled
 * with what the site shows today (built-in menu, one row per page and per
 * image slot). Only inserts: an existing row is never changed, so running it
 * again, or after edits, is harmless.
 */
class ImportSiteContent
{
    /** Built-in menu: route => lang key for the label (site.nav.*). */
    private const MENU_ROUTES = [
        'home' => 'home', 'services.index' => 'services', 'stones.index' => 'stones', 'projects.index' => 'projects',
        'gallery' => 'gallery', 'blog.index' => 'blog', 'faq' => 'faq', 'about' => 'about', 'contact' => 'contact',
    ];

    /**
     * @return array<string, int> what was created, by kind
     */
    public function handle(): array
    {
        return DB::transaction(fn () => [
            'menus' => $this->menus(),
            'pages' => $this->pages(),
            'site_images' => $this->siteImages(),
        ]);
    }

    /**
     * Header and footer menus with the built-in links; an empty mobile menu
     * (it follows the header). Items are only added to a menu created now.
     */
    public function menus(): int
    {
        $created = 0;
        foreach (MenuLocation::cases() as $location) {
            $menu = Menu::query()->firstOrCreate(['location' => $location->value], [
                'options' => $location === MenuLocation::Header
                    ? ['cta_show' => true, 'show_language_switch' => true, 'show_search' => true]
                    : null,
            ]);
            if (! $menu->wasRecentlyCreated) {
                continue;
            }
            $created++;

            if ($location === MenuLocation::Mobile) {
                continue;
            }
            $order = 0;
            foreach (self::MENU_ROUTES as $route => $key) {
                if ($location === MenuLocation::Footer && $route === 'home') {
                    continue;
                }
                $menu->allItems()->create([
                    'label_ar' => __("site.nav.{$key}", [], 'ar'),
                    'label_en' => __("site.nav.{$key}", [], 'en'),
                    'link_type' => MenuLinkType::Route->value,
                    'route_name' => $route,
                    'order' => ++$order,
                ]);
            }
        }

        return $created;
    }

    public function pages(): int
    {
        $created = 0;
        foreach (PageKey::cases() as $key) {
            $created += (int) Page::query()->firstOrCreate(['key' => $key->value])->wasRecentlyCreated;
        }

        return $created;
    }

    public function siteImages(): int
    {
        $created = 0;
        foreach (array_keys(SiteImage::SLOTS) as $slot) {
            $created += (int) SiteImage::query()->firstOrCreate(['slot' => $slot])->wasRecentlyCreated;
        }

        return $created;
    }
}

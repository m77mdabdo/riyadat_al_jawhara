<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Home page layout from Website Content → Homepage (settings.home_sections),
 * with the built-in layout as the default for everything not set: section
 * order and visibility, list sizes, repeater items, hero and CTA options.
 * Section titles are site texts (site.home.*).
 */
class HomeSections
{
    /** Sections below the hero, in their built-in order. */
    public const SECTIONS = ['why', 'services', 'categories', 'projects', 'stones', 'process', 'stats', 'heritage', 'testimonials', 'faq', 'cta'];

    /** Icons offered for the why-choose-us cards. */
    public const ICONS = [
        'heroicon-o-arrows-right-left', 'heroicon-o-squares-2x2', 'heroicon-o-scale', 'heroicon-o-clipboard-document-check',
        'heroicon-o-sparkles', 'heroicon-o-shield-check', 'heroicon-o-truck', 'heroicon-o-wrench-screwdriver',
        'heroicon-o-clock', 'heroicon-o-star', 'heroicon-o-hand-thumb-up', 'heroicon-o-user-group',
        'heroicon-o-home-modern', 'heroicon-o-building-office-2', 'heroicon-o-cube', 'heroicon-o-check-badge',
    ];

    /** Hero overlay strength => gradient classes (the medium one is the built-in look). */
    public const OVERLAYS = [
        'light' => 'from-charcoal/75 to-charcoal/25',
        'medium' => 'from-charcoal/90 to-charcoal/40',
        'strong' => 'from-charcoal/95 to-charcoal/60',
    ];

    /** Background textures offered for the closing call-to-action band. */
    public const CTA_BACKGROUNDS = ['texture-sandstone', 'texture-marble', 'texture-granite', 'texture-quartz'];

    /** Built-in list sizes; null = all active records. */
    private const COUNTS = ['services' => null, 'projects' => 'home_featured_projects', 'stones' => 'home_stones', 'faq' => 'home_faqs'];

    private static function settings(): Setting
    {
        return Setting::current();
    }

    private static function get(string $key, mixed $default = null): mixed
    {
        return self::settings()->option('home_sections', $key, $default);
    }

    /**
     * Enabled sections in display order.
     *
     * @return list<string>
     */
    public static function order(): array
    {
        $saved = array_values(array_intersect((array) self::get('order', []), self::SECTIONS));
        // Sections added in a later version than the saved order go last.
        $order = array_merge($saved, array_diff(self::SECTIONS, $saved));

        return array_values(array_filter($order, fn (string $section) => self::enabled($section)));
    }

    public static function enabled(string $section): bool
    {
        return (bool) self::get("sections.{$section}.enabled", true);
    }

    /** How many records a list section shows (null = all). */
    public static function count(string $section): ?int
    {
        $count = self::get("sections.{$section}.count");
        if (is_numeric($count) && (int) $count > 0) {
            return min((int) $count, 24);
        }
        $config = self::COUNTS[$section] ?? null;

        return $config ? (int) config('site.lists.'.$config) : null;
    }

    /**
     * @return list<array{icon: string, title: string, description: string}>
     */
    public static function whyItems(): array
    {
        $items = self::items('sections.why.items', ['title', 'description']);
        if ($items === null) {
            return array_values((array) __('site.home.why_items'));
        }

        return array_map(fn (array $item) => $item + [
            'icon' => in_array($item['raw']['icon'] ?? null, self::ICONS, true) ? $item['raw']['icon'] : self::ICONS[0],
        ], $items);
    }

    /**
     * @return list<array{title: string, description: string}>
     */
    public static function processSteps(): array
    {
        return self::items('sections.process.items', ['title', 'description']) ?? array_values((array) __('site.process.steps'));
    }

    /**
     * Heritage strip cards; `slot` is the site image shown (Website Content → Site images).
     *
     * @return list<array{name: string, caption: string, slot: string}>
     */
    public static function heritageItems(): array
    {
        $items = self::items('sections.heritage.items', ['name', 'caption']);
        if ($items === null) {
            $items = [];
            foreach ((array) __('site.home.heritage_items') as $key => $item) {
                $items[] = $item + ['slot' => 'heritage-'.$key];
            }

            return $items;
        }

        return array_map(fn (array $item) => $item + ['slot' => (string) ($item['raw']['slot'] ?? '')], $items);
    }

    /**
     * Saved repeater items in the current language (other language as
     * fallback), or null when none are saved (= built-in items).
     *
     * @param  list<string>  $fields
     * @return list<array<string, mixed>>|null
     */
    private static function items(string $key, array $fields): ?array
    {
        $saved = self::get($key);
        if (! is_array($saved) || $saved === []) {
            return null;
        }

        [$primary, $fallback] = app()->getLocale() === 'ar' ? ['ar', 'en'] : ['en', 'ar'];
        $items = [];
        foreach ($saved as $raw) {
            $item = ['raw' => $raw];
            foreach ($fields as $field) {
                $item[$field] = (string) (($raw["{$field}_{$primary}"] ?? null) ?: ($raw["{$field}_{$fallback}"] ?? ''));
            }
            if ($item[$fields[0]] !== '') {
                $items[] = $item;
            }
        }

        return $items;
    }

    public static function overlay(): string
    {
        return self::OVERLAYS[self::get('hero.overlay', 'medium')] ?? self::OVERLAYS['medium'];
    }

    /** The marble video may play behind the hero (when no slide/photo replaces it). */
    public static function videoAllowed(): bool
    {
        return (bool) self::get('hero.video', true);
    }

    public static function heroTitle(): string
    {
        return __('site.home.hero_title') ?: __('site.meta.site_name');
    }

    public static function heroSubtitle(): string
    {
        return __('site.home.hero_subtitle') ?: __('site.meta.tagline');
    }

    /**
     * Primary hero button (when no slide defines one) and whether the
     * WhatsApp button is shown next to it.
     *
     * @return array{label: string, url: string, whatsapp: bool}
     */
    public static function heroButtons(): array
    {
        $locale = app()->getLocale();
        $link = self::get('hero.cta_link');

        return [
            'label' => (string) (self::get('hero.cta_label_'.$locale) ?: __('site.common.request_quote')),
            'url' => is_string($link) && $link !== '' ? (string) LocalizedUrl::link($link) : route('contact'),
            'whatsapp' => (bool) self::get('hero.whatsapp', true),
        ];
    }

    public static function ctaBackground(): string
    {
        $slot = self::get('sections.cta.background', 'texture-sandstone');

        return in_array($slot, self::CTA_BACKGROUNDS, true) ? $slot : 'texture-sandstone';
    }
}

<?php

namespace App\Support;

/**
 * The permission names used by the policies: "{area}.{ability}" for every
 * content area, plus a few special ones. Roles get them in
 * Administration → Roles; app:import-site-content creates them.
 */
class Permissions
{
    public const ABILITIES = ['view', 'create', 'update', 'delete'];

    /** Content areas (policy prefix), grouped as in the navigation. */
    public const AREAS = [
        'leads', 'comments',
        'services', 'stone_types', 'stone_categories', 'stone_attributes',
        'projects', 'testimonials',
        'sliders', 'faqs', 'team_members', 'menus', 'pages', 'site_images', 'site_texts',
        'posts', 'blog_categories', 'tags',
    ];

    /** Special permissions (admin-only by default). */
    public const SPECIAL = ['leads.export', 'settings.manage', 'users.manage', 'activity.view'];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $names = [];
        foreach (self::AREAS as $area) {
            foreach (self::ABILITIES as $ability) {
                $names[] = "{$area}.{$ability}";
            }
        }

        return [...$names, ...self::SPECIAL];
    }

    /**
     * What the built-in editor role could always do: view, create and edit
     * every content area, never delete, nothing special.
     *
     * @return list<string>
     */
    public static function editorDefaults(): array
    {
        $names = [];
        foreach (self::AREAS as $area) {
            foreach (['view', 'create', 'update'] as $ability) {
                $names[] = "{$area}.{$ability}";
            }
        }

        return $names;
    }
}

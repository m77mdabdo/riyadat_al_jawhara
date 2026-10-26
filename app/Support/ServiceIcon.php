<?php

namespace App\Support;

/**
 * The only icons a Service may use. The admin Select is built from this
 * list, and the public views go through resolve() so a value that isn't in
 * it (e.g. a typo saved before the Select existed) renders the default icon
 * instead of throwing "Unable to locate a class or view for component".
 */
class ServiceIcon
{
    public const DEFAULT = 'heroicon-o-cube';

    /**
     * @var array<string, string> Blade component name => admin label
     */
    public const OPTIONS = [
        'heroicon-o-truck' => 'Truck (Supply / Delivery)',
        'heroicon-o-wrench-screwdriver' => 'Wrench & Screwdriver (Installation)',
        'heroicon-o-building-office-2' => 'Building (Facades / Commercial)',
        'heroicon-o-sparkles' => 'Sparkles (Decor / Finishing)',
        'heroicon-o-cube' => 'Cube (Default / Stone Block)',
    ];

    public static function resolve(?string $icon): string
    {
        return array_key_exists((string) $icon, self::OPTIONS) ? $icon : self::DEFAULT;
    }
}

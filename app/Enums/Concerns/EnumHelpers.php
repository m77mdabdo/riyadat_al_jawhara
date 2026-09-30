<?php

namespace App\Enums\Concerns;

/**
 * Shared helpers for string-backed enums whose values are stored as plain
 * strings in the database (columns keep their existing type).
 */
trait EnumHelpers
{
    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

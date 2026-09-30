<?php

namespace App\Enums\Concerns;

/**
 * Select options and display labels for enums with translated labels.
 */
trait HasLabelOptions
{
    use EnumHelpers;

    abstract public function getLabel(?string $locale = null): string;

    /**
     * value => translated label, for selects and filters.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }

    /** Translated label for a stored value; unknown values are shown as-is. */
    public static function labelFor(?string $value, ?string $locale = null): string
    {
        return $value === null ? '' : (self::tryFrom($value)?->getLabel($locale) ?? $value);
    }
}

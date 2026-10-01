<?php

namespace App\Support;

use App\Enums\PageKey;

/**
 * Optional contact-form fields as set in Website Content → Pages → Contact.
 * Name, phone and consent are always shown and required.
 */
class ContactForm
{
    /** Field => built-in [shown, required]. */
    public const FIELDS = [
        'email' => [true, false],
        'city' => [true, false],
        'service_id' => [true, false],
        'stone_type_id' => [true, false],
        'message' => [true, true],
    ];

    public static function shows(string $field): bool
    {
        return (bool) (PageContent::get(PageKey::Contact)?->option("fields.{$field}.show") ?? self::FIELDS[$field][0]);
    }

    /**
     * Public label of a field: the shipped "(optional)" hint is dropped when
     * the field has been made required.
     */
    public static function label(string $field, string $label): string
    {
        return self::requires($field) ? (string) preg_replace('/\s*\((optional|اختياري)\)\s*$/u', '', $label) : $label;
    }

    public static function requires(string $field): bool
    {
        return self::shows($field)
            && (bool) (PageContent::get(PageKey::Contact)?->option("fields.{$field}.required") ?? self::FIELDS[$field][1]);
    }
}

<?php

namespace App\Filament\Pages\Concerns;

use App\Enums\AdminNavigationGroup;
use App\Models\Setting;

/**
 * A Settings sub-page: admins only (as the single Settings page was), listed
 * under Settings in the navigation, saved through EditsSiteSettings.
 */
trait IsSettingsPage
{
    use EditsSiteSettings;

    abstract public static function label(): string;

    /**
     * Settings control every public contact detail and the raw legal HTML,
     * so only admins may open (or save) these pages. Filament checks this for
     * navigation, direct URL access and Livewire requests alike.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->can('update', Setting::class) ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return AdminNavigationGroup::Settings->value;
    }

    public static function getNavigationLabel(): string
    {
        return static::label();
    }

    public function getTitle(): string
    {
        return static::label();
    }

    public function getView(): string
    {
        return 'filament.pages.site-settings-form';
    }
}

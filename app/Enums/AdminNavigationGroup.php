<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabelOptions;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

/**
 * Admin sidebar groups, in display order. Resources and pages return the
 * case value from getNavigationGroup(); the label is translated at render
 * time (admin.nav.*), so it follows the admin's language.
 */
enum AdminNavigationGroup: string implements HasIcon, HasLabel
{
    use HasLabelOptions;

    case Leads = 'leads';
    case Catalogue = 'catalogue';
    case Portfolio = 'portfolio';
    case WebsiteContent = 'website-content';
    case Blog = 'blog';
    case Settings = 'settings';
    case Administration = 'administration';

    public function getLabel(?string $locale = null): string
    {
        return __('admin.nav.'.$this->value, [], $locale);
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Leads => 'heroicon-o-inbox-arrow-down',
            self::Catalogue => 'heroicon-o-cube',
            self::Portfolio => 'heroicon-o-photo',
            self::WebsiteContent => 'heroicon-o-window',
            self::Blog => 'heroicon-o-newspaper',
            self::Settings => 'heroicon-o-cog-6-tooth',
            self::Administration => 'heroicon-o-shield-check',
        };
    }
}

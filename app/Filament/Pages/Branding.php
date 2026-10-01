<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Filament\Pages\Concerns\EditsSiteSettings;
use App\Filament\Support\ImageUpload;
use App\Filament\Support\SiteTextFields;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

/**
 * Logos, favicon, default share image and the site name / tagline.
 * Admins only (as the branding part of Settings was). Brand colours are
 * intentionally not editable (see docs/ADMIN_GUIDE.md).
 */
class Branding extends Page implements HasForms
{
    use EditsSiteSettings;

    protected static string $view = 'filament.pages.site-settings-form';

    protected static ?int $navigationSort = 95;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('update', Setting::class) ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return AdminNavigationGroup::WebsiteContent->value;
    }

    public static function getNavigationLabel(): string
    {
        return __('Branding');
    }

    public function getTitle(): string
    {
        return __('Branding');
    }

    /**
     * @return list<string>
     */
    protected function siteTextKeys(): array
    {
        return ['site.meta.site_name', 'site.meta.tagline'];
    }

    protected function schema(): array
    {
        $image = fn (string $name, string $label, string $help) => ImageUpload::make($name, $name)
            ->label(__($label))
            ->maxSize((int) config('site.uploads.branding_max_kb'))
            ->helperText(__($help).' '.__('JPG, PNG or WebP, up to :size MB.', ['size' => round(config('site.uploads.branding_max_kb') / 1024)]));

        return [
            Forms\Components\Section::make(__('Name'))
                ->description(__('Used in the header, page titles, emails and search results. Leave empty to keep the original wording.'))
                ->schema([
                    SiteTextFields::make('site.meta.site_name', __('Site name')),
                    SiteTextFields::make('site.meta.tagline', __('Tagline'), multiline: true),
                ]),
            Forms\Components\Section::make(__('Logos and icons'))
                ->description(__('Leave empty to keep the built-in logo and share image.'))
                ->schema([
                    $image('logo', 'Logo (dark, for light backgrounds)', 'Transparent PNG, same proportions as the current logo (~294×256 px). Header and error pages.'),
                    $image('logo_light', 'Logo (light, for dark backgrounds)', 'Transparent PNG (white artwork), ~294×256 px. Footer.'),
                    $image('og_image', 'Default share image', '1200×630 px JPG/PNG/WebP. Used when a page has no image of its own.'),
                    $image('favicon', 'Browser icon (favicon)', 'Square PNG, at least 512×512 px. Shown in browser tabs and on phone home screens.'),
                ])->columns(2),
            Forms\Components\Section::make(__('Colours'))
                ->schema([
                    Forms\Components\Placeholder::make('colours')->hiddenLabel()
                        ->content(__('The brand colours are part of the design and checked for readability, so they are not editable here.')),
                ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function mutateSaveData(array $state): array
    {
        // Only uploads (saved as media) and site texts are edited here.
        return [];
    }
}

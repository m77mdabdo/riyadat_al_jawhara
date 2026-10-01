<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Pages\Concerns\IsSettingsPage;
use App\Filament\Support\ImageUpload;
use App\Filament\Support\OptionToggle;
use App\Filament\Support\SiteTextFields;
use App\Support\Seo;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

/**
 * Settings → SEO: title pattern, default description and share image,
 * site-wide noindex and search-console verification.
 */
class SeoSettings extends Page implements HasForms
{
    use IsSettingsPage;

    protected static ?int $navigationSort = 40;

    protected static ?string $slug = 'settings/seo';

    public static function label(): string
    {
        return __('SEO');
    }

    /**
     * @return list<string>
     */
    protected function siteTextKeys(): array
    {
        return ['site.meta.default_description'];
    }

    /**
     * @return array<int, Component>
     */
    protected function schema(): array
    {
        return [
            Forms\Components\Section::make(__('Titles and descriptions'))
                ->description(__('Each page can still override its own title and description.'))
                ->schema([
                    Forms\Components\TextInput::make('seo_options.title_pattern')
                        ->label(__('Page title pattern'))
                        ->placeholder(Seo::DEFAULT_TITLE_PATTERN)
                        ->helperText(__('{page} is the page name, {site} the site name. Empty = “{page} | {site}”.'))
                        ->rule('regex:/\{page\}/')
                        ->validationMessages(['regex' => __('The pattern must contain {page}.')])
                        ->maxLength(80)
                        ->extraInputAttributes(['dir' => 'ltr']),
                    SiteTextFields::make('site.meta.default_description', __('Default description'), multiline: true),
                    ImageUpload::make('og_image', 'og_image', '1200×630', '1.91:1')->label(__('Default share image')),
                ]),
            Forms\Components\Section::make(__('Search engines'))
                ->schema([
                    OptionToggle::make('seo_options.noindex', false)
                        ->label(__('Hide the whole site from search engines'))
                        ->helperText(__('Only for a staging copy or before launch. Leave off on the live site.')),
                    Forms\Components\TextInput::make('seo_options.google_verification')
                        ->label(__('Google Search Console verification code'))
                        ->helperText(__('Only the code from content="…" of the HTML-tag method.'))
                        ->regex(Seo::VERIFICATION_PATTERN)->maxLength(120)->extraInputAttributes(['dir' => 'ltr']),
                    Forms\Components\TextInput::make('seo_options.bing_verification')
                        ->label(__('Bing Webmaster verification code'))
                        ->regex(Seo::VERIFICATION_PATTERN)->maxLength(120)->extraInputAttributes(['dir' => 'ltr']),
                ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function mutateSaveData(array $state): array
    {
        return ['seo_options' => $state['seo_options'] ?? []];
    }
}

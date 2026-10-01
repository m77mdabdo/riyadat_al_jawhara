<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Filament\Pages\Concerns\EditsSiteSettings;
use App\Filament\Resources\MenuResource;
use App\Filament\Support\OptionToggle;
use App\Filament\Support\SiteTextFields;
use App\Models\SiteText;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

/**
 * Footer: which blocks are shown, the short about text, headings and the
 * copyright line. Footer links are the Footer menu.
 */
class FooterSettings extends Page implements HasForms
{
    use EditsSiteSettings;

    protected static string $view = 'filament.pages.site-settings-form';

    protected static ?int $navigationSort = 80;

    protected static ?string $slug = 'footer';

    private const TEXTS = [
        'site.footer.about' => 'Text under the logo',
        'site.footer.quick_links' => 'Links column heading',
        'site.footer.contact_info' => 'Contact column heading',
        'site.footer.follow_us' => 'Social column heading',
        'site.footer.rights_reserved' => 'Rights reserved',
    ];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('update', SiteText::class) ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return AdminNavigationGroup::WebsiteContent->value;
    }

    public static function getNavigationLabel(): string
    {
        return __('Footer');
    }

    public function getTitle(): string
    {
        return __('Footer');
    }

    /**
     * @return list<string>
     */
    protected function siteTextKeys(): array
    {
        return array_keys(self::TEXTS);
    }

    /**
     * @return array<int, Component>
     */
    protected function schema(): array
    {
        return [
            Forms\Components\Section::make(__('Blocks'))
                ->description(new HtmlString(e(__('Footer links and columns are edited in')).' <a class="underline" href="'.e(MenuResource::getUrl()).'">'.e(__('Menus')).'</a>. '.e(__('Contact details and social links come from Settings.'))))
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        OptionToggle::make('footer_options.show_about')->label(__('About text')),
                        OptionToggle::make('footer_options.show_contact')->label(__('Contact details')),
                        OptionToggle::make('footer_options.show_social')->label(__('Social links')),
                        OptionToggle::make('footer_options.show_legal')->label(__('Privacy and terms links')),
                    ]),
                ]),
            Forms\Components\Section::make(__('Texts'))
                ->description(__('Leave a text empty to keep the original wording, shown in grey.'))
                ->schema([
                    ...collect(self::TEXTS)->map(fn (string $label, string $key) => SiteTextFields::make($key, __($label), multiline: $key === 'site.footer.about'))->values()->all(),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('footer_options.copyright_ar')->label(__('Copyright line (AR)'))->maxLength(200)->extraInputAttributes(['dir' => 'rtl']),
                        Forms\Components\TextInput::make('footer_options.copyright_en')->label(__('Copyright line (EN)'))->maxLength(200),
                    ]),
                    Forms\Components\Placeholder::make('copyright_help')->hiddenLabel()
                        ->content(__('Write {year} where the current year should appear. Empty = “© year, site name — rights reserved”.')),
                ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function mutateSaveData(array $state): array
    {
        return ['footer_options' => $state['footer_options'] ?? []];
    }
}

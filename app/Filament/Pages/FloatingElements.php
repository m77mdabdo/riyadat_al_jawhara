<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Filament\Pages\Concerns\EditsSiteSettings;
use App\Filament\Support\OptionToggle;
use App\Filament\Support\SiteTextFields;
use App\Models\SiteText;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

/**
 * The WhatsApp button, back-to-top button and cookie banner texts.
 */
class FloatingElements extends Page implements HasForms
{
    use EditsSiteSettings;

    protected static string $view = 'filament.pages.site-settings-form';

    protected static ?int $navigationSort = 85;

    protected static ?string $slug = 'floating-elements';

    private const WHATSAPP_TEXTS = [
        'site.whatsapp_default_message' => 'Message typed for the visitor',
        'site.whatsapp_tooltip' => 'Bubble text',
        'site.whatsapp_aria_label' => 'Button name for screen readers',
    ];

    private const CONSENT_TEXTS = [
        'site.consent.title' => 'Title',
        'site.consent.message' => 'Message',
        'site.consent.accept' => 'Accept button',
        'site.consent.decline' => 'Decline button',
        'site.consent.settings' => 'Footer link',
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
        return __('Floating elements');
    }

    public function getTitle(): string
    {
        return __('Floating elements');
    }

    /**
     * @return list<string>
     */
    protected function siteTextKeys(): array
    {
        return [...array_keys(self::WHATSAPP_TEXTS), ...array_keys(self::CONSENT_TEXTS)];
    }

    /**
     * @return array<int, Component>
     */
    protected function schema(): array
    {
        return [
            Forms\Components\Section::make(__('WhatsApp button'))
                ->description(__('Shown only when a WhatsApp number is set in Settings.'))
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        OptionToggle::make('floating_options.whatsapp_show')->label(__('Show the WhatsApp button')),
                        Forms\Components\Select::make('floating_options.whatsapp_position')->label(__('Position'))
                            ->options([
                                'right' => __('Right, in both languages (default)'),
                                'end' => __('End of the reading direction (left in Arabic)'),
                                'start' => __('Start of the reading direction (left in English)'),
                            ])
                            ->placeholder(__('Right, in both languages (default)')),
                    ]),
                    ...collect(self::WHATSAPP_TEXTS)->map(fn (string $label, string $key) => SiteTextFields::make($key, __($label), multiline: $key !== 'site.whatsapp_aria_label'))->values()->all(),
                ]),
            Forms\Components\Section::make(__('Back-to-top button'))
                ->schema([
                    OptionToggle::make('floating_options.back_to_top')->label(__('Show the back-to-top button while scrolling')),
                ]),
            Forms\Components\Section::make(__('Cookie banner'))
                ->description(__('Shown only when an analytics service is configured; nothing is tracked before the visitor accepts.'))
                ->collapsible()
                ->schema(collect(self::CONSENT_TEXTS)->map(fn (string $label, string $key) => SiteTextFields::make($key, __($label), multiline: $key === 'site.consent.message'))->values()->all()),
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function mutateSaveData(array $state): array
    {
        return ['floating_options' => $state['floating_options'] ?? []];
    }
}

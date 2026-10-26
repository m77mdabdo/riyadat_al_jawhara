<?php

namespace App\Filament\Pages;

use App\Filament\Support\ImageUpload;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    /**
     * Settings control every public contact detail and the raw legal HTML,
     * so only admins may open (or save) this page. Filament checks this for
     * navigation, direct URL access and Livewire requests alike.
     */
    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public function getTitle(): string
    {
        return __('Settings');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill($this->getSetting()->attributesToArray());
    }

    /**
     * The single settings row, needed so the media upload fields can load and
     * save their files through Spatie Media Library.
     */
    public function getSetting(): Setting
    {
        return Setting::editable();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        $image = fn (string $name, string $collection, string $label, string $help) => ImageUpload::make($name, $collection)
            ->label($label)
            ->maxSize(2048)
            ->helperText(__($help).' '.__('JPG, PNG or WebP, up to 2 MB.'));

        return $form
            ->model($this->getSetting())
            ->schema([
                Forms\Components\Section::make(__('Branding'))
                    ->description(__('Leave empty to keep the built-in logo and share image.'))
                    ->schema([
                        $image('logo', 'logo', 'Logo (dark, for light backgrounds)', 'Transparent PNG, same proportions as the current logo (~294×256 px). Header and error pages.'),
                        $image('logo_light', 'logo_light', 'Logo (light, for dark backgrounds)', 'Transparent PNG (white artwork), ~294×256 px. Footer.'),
                        $image('og_image', 'og_image', 'Default share image', '1200×630 px JPG/PNG/WebP. Used when a page has no image of its own.'),
                    ])->columns(3),

                Forms\Components\Section::make(__('Contact'))
                    ->schema([
                        Forms\Components\TextInput::make('phone')->tel(),
                        Forms\Components\TextInput::make('whatsapp')
                            ->helperText(__('With country code, e.g. 966501234567. Spaces and "+" are removed on save.'))
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? preg_replace('/\D/', '', $state) : null),
                        Forms\Components\TextInput::make('email')->email(),
                    ])->columns(3),

                Forms\Components\Section::make(__('Address & Hours'))
                    ->schema([
                        Forms\Components\Tabs::make('AddressTranslations')
                            ->columnSpanFull()
                            ->tabs([
                                Forms\Components\Tabs\Tab::make(__('English'))
                                    ->schema([
                                        Forms\Components\TextInput::make('address_en')->label(__('Address (EN)')),
                                        Forms\Components\TextInput::make('working_hours_en')->label(__('Working Hours (EN)')),
                                    ]),
                                Forms\Components\Tabs\Tab::make(__('Arabic'))
                                    ->schema([
                                        Forms\Components\TextInput::make('address_ar')->label(__('العنوان (AR)')),
                                        Forms\Components\TextInput::make('working_hours_ar')->label(__('ساعات العمل (AR)')),
                                    ]),
                            ]),
                        Forms\Components\TextInput::make('map_lat')->numeric()->label(__('Map Latitude')),
                        Forms\Components\TextInput::make('map_lng')->numeric()->label(__('Map Longitude')),
                    ])->columns(2),

                Forms\Components\Section::make(__('About / Vision / Mission'))
                    ->schema([
                        Forms\Components\Tabs::make('AboutTranslations')
                            ->columnSpanFull()
                            ->tabs([
                                Forms\Components\Tabs\Tab::make(__('English'))
                                    ->schema([
                                        Forms\Components\Textarea::make('about_en')->label(__('About (EN)'))->rows(3),
                                        Forms\Components\Textarea::make('vision_en')->label(__('Vision (EN)'))->rows(2),
                                        Forms\Components\Textarea::make('mission_en')->label(__('Mission (EN)'))->rows(2),
                                    ]),
                                Forms\Components\Tabs\Tab::make(__('Arabic'))
                                    ->schema([
                                        Forms\Components\Textarea::make('about_ar')->label(__('من نحن (AR)'))->rows(3),
                                        Forms\Components\Textarea::make('vision_ar')->label(__('الرؤية (AR)'))->rows(2),
                                        Forms\Components\Textarea::make('mission_ar')->label(__('الرسالة (AR)'))->rows(2),
                                    ]),
                            ]),
                    ]),

                Forms\Components\Section::make(__('Legal Pages'))
                    ->schema([
                        Forms\Components\Tabs::make('LegalTranslations')
                            ->columnSpanFull()
                            ->tabs([
                                Forms\Components\Tabs\Tab::make(__('English'))
                                    ->schema([
                                        Forms\Components\RichEditor::make('privacy_policy_en')->label(__('Privacy Policy (EN)')),
                                        Forms\Components\RichEditor::make('terms_en')->label(__('Terms of Use (EN)')),
                                    ]),
                                Forms\Components\Tabs\Tab::make(__('Arabic'))
                                    ->schema([
                                        Forms\Components\RichEditor::make('privacy_policy_ar')->label(__('سياسة الخصوصية (AR)')),
                                        Forms\Components\RichEditor::make('terms_ar')->label(__('شروط الاستخدام (AR)')),
                                    ]),
                            ]),
                    ]),

                Forms\Components\Section::make(__('Statistics (home page)'))
                    ->description(__('Real figures only. Leave a field empty to hide it; the section is hidden when all are empty.'))
                    ->schema([
                        Forms\Components\TextInput::make('stat_projects_completed')->label(__('Projects completed'))->numeric()->minValue(1)->maxValue(100000),
                        Forms\Components\TextInput::make('stat_years_experience')->label(__('Years of experience'))->numeric()->minValue(1)->maxValue(200),
                        Forms\Components\TextInput::make('stat_cities_served')->label(__('Cities served'))->numeric()->minValue(1)->maxValue(1000),
                        Forms\Components\TextInput::make('stat_clients_served')->label(__('Clients served'))->numeric()->minValue(1)->maxValue(1000000),
                    ])->columns(4),

                Forms\Components\Section::make(__('Social Links'))
                    ->schema([
                        Forms\Components\TextInput::make('facebook_url')->url(),
                        Forms\Components\TextInput::make('instagram_url')->url(),
                        Forms\Components\TextInput::make('tiktok_url')->url(),
                        Forms\Components\TextInput::make('snapchat_url')->url(),
                        Forms\Components\TextInput::make('linkedin_url')->label(__('LinkedIn URL'))->url(),
                        Forms\Components\TextInput::make('x_url')->label(__('X (Twitter) URL'))->url(),
                        Forms\Components\TextInput::make('youtube_url')->label(__('YouTube URL'))->url(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $setting = $this->getSetting();
        $setting->fill($this->form->getState())->save();
        $this->form->model($setting)->saveRelationships();
        // Media uploads don't touch the settings row, so flush explicitly.
        Setting::flushCache();

        Notification::make()
            ->title(__('Settings saved'))
            ->success()
            ->send();
    }
}

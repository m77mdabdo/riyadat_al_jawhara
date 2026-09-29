<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(Setting::current()->attributesToArray());
    }

    /**
     * The single settings row, needed so the media upload fields can load and
     * save their files through Spatie Media Library.
     */
    public function getSetting(): Setting
    {
        return Setting::current();
    }

    public function form(Forms\Form $form): Forms\Form
    {
        $image = fn (string $name, string $collection, string $label, string $help) => SpatieMediaLibraryFileUpload::make($name)
            ->label($label)
            ->collection($collection)
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(2048)
            ->helperText($help);

        return $form
            ->model($this->getSetting())
            ->schema([
                Forms\Components\Section::make('Branding')
                    ->description('Leave empty to keep the built-in logo and share image.')
                    ->schema([
                        $image('logo', 'logo', 'Logo (dark, for light backgrounds)', 'Transparent PNG, same proportions as the current logo (~294×256 px). Header and error pages.'),
                        $image('logo_light', 'logo_light', 'Logo (light, for dark backgrounds)', 'Transparent PNG (white artwork), ~294×256 px. Footer.'),
                        $image('og_image', 'og_image', 'Default share image', '1200×630 px JPG/PNG/WebP. Used when a page has no image of its own.'),
                    ])->columns(3),

                Forms\Components\Section::make('Contact')
                    ->schema([
                        Forms\Components\TextInput::make('phone')->tel(),
                        Forms\Components\TextInput::make('whatsapp')
                            ->helperText('With country code, e.g. 966501234567. Spaces and "+" are removed on save.')
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? preg_replace('/\D/', '', $state) : null),
                        Forms\Components\TextInput::make('email')->email(),
                    ])->columns(3),

                Forms\Components\Section::make('Address & Hours')
                    ->schema([
                        Forms\Components\Tabs::make('AddressTranslations')
                            ->columnSpanFull()
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('English')
                                    ->schema([
                                        Forms\Components\TextInput::make('address_en')->label('Address (EN)'),
                                        Forms\Components\TextInput::make('working_hours_en')->label('Working Hours (EN)'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Arabic')
                                    ->schema([
                                        Forms\Components\TextInput::make('address_ar')->label('العنوان (AR)'),
                                        Forms\Components\TextInput::make('working_hours_ar')->label('ساعات العمل (AR)'),
                                    ]),
                            ]),
                        Forms\Components\TextInput::make('map_lat')->numeric()->label('Map Latitude'),
                        Forms\Components\TextInput::make('map_lng')->numeric()->label('Map Longitude'),
                    ])->columns(2),

                Forms\Components\Section::make('About / Vision / Mission')
                    ->schema([
                        Forms\Components\Tabs::make('AboutTranslations')
                            ->columnSpanFull()
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('English')
                                    ->schema([
                                        Forms\Components\Textarea::make('about_en')->label('About (EN)')->rows(3),
                                        Forms\Components\Textarea::make('vision_en')->label('Vision (EN)')->rows(2),
                                        Forms\Components\Textarea::make('mission_en')->label('Mission (EN)')->rows(2),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Arabic')
                                    ->schema([
                                        Forms\Components\Textarea::make('about_ar')->label('من نحن (AR)')->rows(3),
                                        Forms\Components\Textarea::make('vision_ar')->label('الرؤية (AR)')->rows(2),
                                        Forms\Components\Textarea::make('mission_ar')->label('الرسالة (AR)')->rows(2),
                                    ]),
                            ]),
                    ]),

                Forms\Components\Section::make('Legal Pages')
                    ->schema([
                        Forms\Components\Tabs::make('LegalTranslations')
                            ->columnSpanFull()
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('English')
                                    ->schema([
                                        Forms\Components\RichEditor::make('privacy_policy_en')->label('Privacy Policy (EN)'),
                                        Forms\Components\RichEditor::make('terms_en')->label('Terms of Use (EN)'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Arabic')
                                    ->schema([
                                        Forms\Components\RichEditor::make('privacy_policy_ar')->label('سياسة الخصوصية (AR)'),
                                        Forms\Components\RichEditor::make('terms_ar')->label('شروط الاستخدام (AR)'),
                                    ]),
                            ]),
                    ]),

                Forms\Components\Section::make('Social Links')
                    ->schema([
                        Forms\Components\TextInput::make('facebook_url')->url(),
                        Forms\Components\TextInput::make('instagram_url')->url(),
                        Forms\Components\TextInput::make('tiktok_url')->url(),
                        Forms\Components\TextInput::make('snapchat_url')->url(),
                        Forms\Components\TextInput::make('linkedin_url')->label('LinkedIn URL')->url(),
                        Forms\Components\TextInput::make('x_url')->label('X (Twitter) URL')->url(),
                        Forms\Components\TextInput::make('youtube_url')->label('YouTube URL')->url(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $setting = $this->getSetting();
        $setting->update($this->form->getState());
        $this->form->model($setting)->saveRelationships();

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}

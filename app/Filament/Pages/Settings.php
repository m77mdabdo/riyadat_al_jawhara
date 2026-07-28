<?php

namespace App\Filament\Pages;

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

    public function mount(): void
    {
        $this->form->fill(Setting::current()->toArray());
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contact')
                    ->schema([
                        Forms\Components\TextInput::make('phone')->tel(),
                        Forms\Components\TextInput::make('whatsapp')
                            ->helperText('Digits only, with country code, e.g. 966501234567'),
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

                Forms\Components\Section::make('Social Links')
                    ->schema([
                        Forms\Components\TextInput::make('facebook_url')->url(),
                        Forms\Components\TextInput::make('instagram_url')->url(),
                        Forms\Components\TextInput::make('tiktok_url')->url(),
                        Forms\Components\TextInput::make('snapchat_url')->url(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Setting::current()->update($this->form->getState());

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}

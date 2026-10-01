<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\IsSettingsPage;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

/**
 * Settings → General: the phone, WhatsApp and email used across the site
 * and in emails, and the company introduction (also editable on Pages → About).
 */
class Settings extends Page implements HasForms
{
    use IsSettingsPage;

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.site-settings-form';

    public static function label(): string
    {
        return __('General');
    }

    /**
     * @return array<int, Component>
     */
    protected function schema(): array
    {
        return [
            Forms\Components\Section::make(__('Contact'))
                ->description(__('Shown in the header, footer, contact page, buttons and emails.'))
                ->schema([
                    Forms\Components\TextInput::make('phone')->label(__('Phone'))->tel()->maxLength(30),
                    Forms\Components\TextInput::make('whatsapp')
                        ->label(__('WhatsApp'))
                        ->helperText(__('With country code, e.g. 966501234567. Spaces and "+" are removed on save.'))
                        ->maxLength(30)
                        ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? preg_replace('/\D/', '', $state) : null),
                    Forms\Components\TextInput::make('email')->label(__('Email'))->email()->maxLength(255),
                ])->columns(3),

            Forms\Components\Section::make(__('About / Vision / Mission'))
                ->schema([
                    Forms\Components\Tabs::make('AboutTranslations')
                        ->columnSpanFull()
                        ->tabs([
                            Forms\Components\Tabs\Tab::make(__('Arabic'))
                                ->schema([
                                    Forms\Components\Textarea::make('about_ar')->label(__('About (AR)'))->rows(3),
                                    Forms\Components\Textarea::make('vision_ar')->label(__('Vision (AR)'))->rows(2),
                                    Forms\Components\Textarea::make('mission_ar')->label(__('Mission (AR)'))->rows(2),
                                ]),
                            Forms\Components\Tabs\Tab::make(__('English'))
                                ->schema([
                                    Forms\Components\Textarea::make('about_en')->label(__('About (EN)'))->rows(3),
                                    Forms\Components\Textarea::make('vision_en')->label(__('Vision (EN)'))->rows(2),
                                    Forms\Components\Textarea::make('mission_en')->label(__('Mission (EN)'))->rows(2),
                                ]),
                        ]),
                ]),
        ];
    }
}

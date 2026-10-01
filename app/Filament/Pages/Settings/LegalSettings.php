<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Pages\Concerns\IsSettingsPage;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

/**
 * Settings → Legal: privacy policy and terms of use (also on Pages; links in
 * the footer can be hidden in Website Content → Footer).
 */
class LegalSettings extends Page implements HasForms
{
    use IsSettingsPage;

    protected static ?int $navigationSort = 80;

    protected static ?string $slug = 'settings/legal';

    public static function label(): string
    {
        return __('Legal Pages');
    }

    /**
     * @return array<int, Component>
     */
    protected function schema(): array
    {
        $editor = fn (string $name, string $label) => Forms\Components\RichEditor::make($name)->label(__($label))
            ->disableToolbarButtons(['attachFiles', 'codeBlock'])
            ->extraInputAttributes(['dir' => str_ends_with($name, '_ar') ? 'rtl' : 'ltr']);

        return [
            Forms\Components\Section::make(__('Legal Pages'))
                ->description(__('Formatting, links and lists are kept; scripts and styles are removed when shown.'))
                ->schema([
                    Forms\Components\Tabs::make('LegalTranslations')->tabs([
                        Forms\Components\Tabs\Tab::make(__('Arabic'))->schema([
                            $editor('privacy_policy_ar', 'Privacy Policy (AR)'),
                            $editor('terms_ar', 'Terms of Use (AR)'),
                        ]),
                        Forms\Components\Tabs\Tab::make(__('English'))->schema([
                            $editor('privacy_policy_en', 'Privacy Policy (EN)'),
                            $editor('terms_en', 'Terms of Use (EN)'),
                        ]),
                    ]),
                ]),
        ];
    }
}

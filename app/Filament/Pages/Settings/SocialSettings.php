<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Pages\Concerns\IsSettingsPage;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

/**
 * Settings → Social: profile links shown in the footer and in search results.
 */
class SocialSettings extends Page implements HasForms
{
    use IsSettingsPage;

    protected static ?int $navigationSort = 30;

    protected static ?string $slug = 'settings/social';

    public static function label(): string
    {
        return __('Social Links');
    }

    /**
     * @return array<int, Component>
     */
    protected function schema(): array
    {
        $url = fn (string $name, string $label) => Forms\Components\TextInput::make($name)->label(__($label))->url()->maxLength(255)
            ->placeholder('https://')->extraInputAttributes(['dir' => 'ltr']);

        return [
            Forms\Components\Section::make(__('Social Links'))
                ->description(__('Only filled links are shown, with their icon, in the footer.'))
                ->schema([
                    $url('facebook_url', 'Facebook URL'),
                    $url('instagram_url', 'Instagram URL'),
                    $url('tiktok_url', 'TikTok URL'),
                    $url('snapchat_url', 'Snapchat URL'),
                    $url('linkedin_url', 'LinkedIn URL'),
                    $url('x_url', 'X (Twitter) URL'),
                    $url('youtube_url', 'YouTube URL'),
                ])->columns(2),
        ];
    }
}

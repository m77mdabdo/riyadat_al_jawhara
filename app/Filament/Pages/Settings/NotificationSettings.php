<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Pages\Concerns\IsSettingsPage;
use App\Filament\Support\SiteTextFields;
use Filament\Forms;
use Filament\Forms\Components\Component;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

/**
 * Settings → Notifications: who receives new requests, the automatic reply
 * to visitors, comment emails and the daily follow-up digest time.
 */
class NotificationSettings extends Page implements HasForms
{
    use IsSettingsPage;

    protected static ?int $navigationSort = 60;

    protected static ?string $slug = 'settings/notifications';

    /** Auto-reply texts: fixed wording only, never the visitor's own text. */
    private const AUTO_REPLY_TEXTS = [
        'site.mail.auto_reply.subject' => 'Subject',
        'site.mail.auto_reply.intro' => 'Opening paragraph',
        'site.mail.auto_reply.note' => 'Closing note',
    ];

    public static function label(): string
    {
        return __('Notifications');
    }

    /**
     * @return list<string>
     */
    protected function siteTextKeys(): array
    {
        return array_keys(self::AUTO_REPLY_TEXTS);
    }

    /**
     * @return array<int, Component>
     */
    protected function schema(): array
    {
        return [
            Forms\Components\Section::make(__('New requests'))
                ->description(__('Administrators and the company email (Settings → General) always receive new requests.'))
                ->schema([
                    Forms\Components\TagsInput::make('notification_options.lead_recipients')
                        ->label(__('Also send new requests to'))
                        ->placeholder(__('Type an email and press Enter'))
                        ->nestedRecursiveRules(['email:rfc', 'max:255'])
                        ->splitKeys(['Tab', ',', ' ']),
                ]),
            Forms\Components\Section::make(__('Automatic reply to visitors'))
                ->schema([
                    Forms\Components\Toggle::make('lead_auto_reply')
                        ->label(__('Send an automatic confirmation to visitors who leave an email'))
                        ->helperText(__('Only sent once a phone or WhatsApp number is set in Settings → General.')),
                    ...collect(self::AUTO_REPLY_TEXTS)->map(fn (string $label, string $key) => SiteTextFields::make($key, __($label), multiline: $key !== 'site.mail.auto_reply.subject'))->values()->all(),
                ]),
            Forms\Components\Section::make(__('Staff emails'))
                ->schema([
                    Forms\Components\Toggle::make('comment_mail_notifications')
                        ->label(__('Email staff about new comments (the admin bell always shows them)')),
                    Forms\Components\TextInput::make('notification_options.digest_time')
                        ->label(__('Daily follow-up digest time (Riyadh)'))
                        ->placeholder((string) config('site.notifications.digest_time'))
                        ->regex('/^([01]\d|2[0-3]):[0-5]\d$/')
                        ->helperText(__('24-hour time, e.g. 08:00.'))
                        ->extraInputAttributes(['dir' => 'ltr']),
                ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    protected function mutateSaveData(array $state): array
    {
        $options = $state['notification_options'] ?? [];
        $options['lead_recipients'] = array_values(array_unique(array_map('strtolower', array_filter((array) ($options['lead_recipients'] ?? [])))));

        return [
            'lead_auto_reply' => (bool) ($state['lead_auto_reply'] ?? false),
            'comment_mail_notifications' => (bool) ($state['comment_mail_notifications'] ?? false),
            'notification_options' => $options,
        ];
    }
}

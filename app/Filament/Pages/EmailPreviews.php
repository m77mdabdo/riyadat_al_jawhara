<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Models\Setting;
use App\Support\EmailPreviews as Previews;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;

/**
 * Admin-only preview of every email in Arabic and English, with sample data,
 * and a "send test" that only accepts the configured test address.
 */
class EmailPreviews extends Page
{
    protected static string $view = 'filament.pages.email-previews';

    protected static ?int $navigationSort = 40;

    public string $template = 'lead_new';

    public string $locale = 'ar';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('update', Setting::class) ?? false;
    }

    public static function getNavigationLabel(): string
    {
        return __('Email previews');
    }

    public static function getNavigationGroup(): ?string
    {
        return AdminNavigationGroup::Administration->value;
    }

    public function getTitle(): string
    {
        return __('Email previews');
    }

    /**
     * @return array<string, string>
     */
    public function templateOptions(): array
    {
        return array_map(fn (string $label) => __($label), Previews::options());
    }

    public function previewHtml(): string
    {
        $template = in_array($this->template, Previews::TEMPLATES, true) ? $this->template : 'lead_new';
        $locale = in_array($this->locale, config('locales.supported'), true) ? $this->locale : 'ar';

        return app(Previews::class)->html($template, $locale);
    }

    public function previewSubject(): string
    {
        return app(Previews::class)->subject($this->template, $this->locale);
    }

    protected function getHeaderActions(): array
    {
        $recipient = (string) config('mail.test_recipient');

        return [
            Action::make('sendTest')
                ->label(__('Send test to…'))
                ->icon('heroicon-o-paper-airplane')
                ->disabled($recipient === '')
                ->tooltip($recipient === '' ? __('Set MAIL_TEST_RECIPIENT in .env to enable test emails.') : null)
                ->form([
                    Forms\Components\TextInput::make('to')
                        ->label(__('Email'))
                        ->email()
                        ->required()
                        ->default($recipient)
                        ->in([$recipient])
                        ->validationMessages(['in' => __('Test emails can only go to the configured test address.')]),
                ])
                ->action(function (array $data): void {
                    try {
                        app(Previews::class)->sendTest($this->template, $this->locale, $data['to']);
                        Notification::make()->title(__('Test email sent'))->success()->send();
                    } catch (Throwable $e) {
                        report($e);
                        Notification::make()->title(__('Sending failed'))->body($e::class)->danger()->send();
                    }
                }),
        ];
    }
}

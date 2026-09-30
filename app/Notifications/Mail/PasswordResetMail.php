<?php

namespace App\Notifications\Mail;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * Branded, localized content for the admin password-reset email (Filament's
 * ResetPassword notification; registered with ResetPassword::toMailUsing in
 * AppServiceProvider). It is sent in the user's admin language.
 */
class PasswordResetMail
{
    public static function build(string $url): MailMessage
    {
        $expiry = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject(__('site.mail.reset.subject'))
            ->greeting(__('site.mail.reset.heading'))
            ->line(__('site.mail.reset.intro'))
            ->action(__('site.mail.reset.action'), $url)
            ->line(__('site.mail.reset.expiry', ['count' => $expiry]))
            ->line(__('site.mail.reset.ignore'))
            ->markdown('notifications::email', ['preheader' => __('site.mail.reset.preheader')]);
    }
}

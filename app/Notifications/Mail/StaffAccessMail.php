<?php

namespace App\Notifications\Mail;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * Branded content for a team invitation (new dashboard account) or a fresh
 * "choose a password" link for an existing one. The link opens the admin's
 * own password page; no password is ever sent by email.
 */
class StaffAccessMail
{
    public static function build(string $url, string $name, string $email, string $roles, bool $invitation): MailMessage
    {
        $key = $invitation ? 'site.mail.staff.invite' : 'site.mail.staff.link';
        $expiry = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        $message = (new MailMessage)
            ->subject(__("{$key}.subject", ['app' => config('app.name')]))
            ->greeting(__('site.mail.staff.greeting', ['name' => $name]))
            ->line(__("{$key}.intro", ['roles' => $roles]));

        if ($invitation) {
            $message->line(__('site.mail.staff.invite.account', ['email' => $email]));
        }

        return $message
            ->action(__("{$key}.action"), $url)
            ->line(__('site.mail.staff.expiry', ['count' => $expiry]))
            ->line(__('site.mail.staff.ignore'))
            ->markdown('notifications::email', ['preheader' => __("{$key}.preheader")]);
    }
}

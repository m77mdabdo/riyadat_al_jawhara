<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Models\Setting;
use App\Notifications\Concerns\ReliableDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Str;
use Throwable;

/**
 * Confirmation to the visitor who left an email address, in the language they
 * used on the site. Toggle: Settings → lead_auto_reply.
 *
 * Anti-relay rule: nothing the visitor typed is echoed back except their
 * name, and only when it looks like a name (no links, max 60 characters).
 * The message, city etc. are never included, so the form can't be used to
 * send arbitrary text to arbitrary addresses.
 *
 * Only sent once the company's phone or WhatsApp is set in Settings: the
 * reply's purpose is to confirm and give the visitor a way to reach us, and
 * a half-configured install (staging, demo) should never email visitors.
 */
class LeadAutoReplyNotification extends Notification implements ShouldQueue
{
    use Queueable, ReliableDelivery;

    public function __construct(public Lead $lead)
    {
        $this->configureDelivery();
    }

    public static function dispatchFor(Lead $lead): void
    {
        try {
            $email = $lead->email;
            $settings = Setting::current();
            $reachable = filled($settings->phone) || filled($settings->whatsapp_url);
            if (! $settings->lead_auto_reply || ! $reachable || blank($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email)) {
                return;
            }

            NotificationFacade::route('mail', $email)
                ->notify((new self($lead))->locale($lead->locale ?: config('locales.default')));
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead->loadMissing(['service', 'stoneType', 'project']);
        $name = self::safeName($lead->name);

        return (new MailMessage)
            ->subject(__('site.mail.auto_reply.subject'))
            ->markdown('mail.leads.auto-reply', [
                'preheader' => __('site.mail.auto_reply.preheader'),
                'greeting' => $name ? __('site.mail.auto_reply.greeting', ['name' => $name]) : __('site.mail.auto_reply.greeting_generic'),
                'topic' => $lead->stoneType?->name ?: $lead->service?->name ?: $lead->project?->title,
            ]);
    }

    /** The visitor's name if it is plainly a name; null otherwise. */
    public static function safeName(?string $name): ?string
    {
        $name = trim((string) $name);

        if ($name === '' || mb_strlen($name) > 60 || preg_match('~https?://|www\.|\.[a-z]{2,}(/|\b)|@|<|>~iu', $name)) {
            return null;
        }

        return Str::squish($name);
    }
}

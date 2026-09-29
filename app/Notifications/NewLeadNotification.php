<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Throwable;

/**
 * Emails the company when a new lead arrives from the website.
 *
 * Recipients: the company email in Settings plus every admin user
 * (de-duplicated). Queued, so a slow or failing mail server never delays or
 * breaks the visitor's form submission. With MAIL_MAILER=log the message is
 * written to the log once a queue worker processes it.
 */
class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead)
    {
        $this->afterCommit();
    }

    public static function dispatchFor(Lead $lead): void
    {
        try {
            $admins = User::query()
                ->whereHas('roles', fn ($query) => $query->where('name', 'admin'))
                ->get();

            if ($admins->isNotEmpty()) {
                NotificationFacade::send($admins, new self($lead));
            }

            $companyEmail = Setting::current()->email;

            if (filled($companyEmail) && ! $admins->contains(fn (User $user) => strcasecmp($user->email, $companyEmail) === 0)) {
                NotificationFacade::route('mail', $companyEmail)->notify(new self($lead));
            }
        } catch (Throwable $e) {
            // The lead is already saved; a notification problem must never
            // turn a successful submission into an error page.
            report($e);
        }
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead->loadMissing(['service', 'stoneType', 'project']);
        $dash = '—';

        $message = (new MailMessage)
            ->subject(__('site.lead_mail.subject', ['name' => $lead->name]))
            ->greeting(__('site.lead_mail.greeting'))
            ->line(__('site.lead_mail.intro'))
            ->line(__('site.lead_mail.name').': '.$lead->name)
            ->line(__('site.lead_mail.phone').': '.$lead->phone)
            ->line(__('site.lead_mail.email').': '.($lead->email ?: $dash))
            ->line(__('site.lead_mail.city').': '.($lead->city ?: $dash))
            ->line(__('site.lead_mail.service').': '.($lead->service?->name_en ?? $dash))
            ->line(__('site.lead_mail.stone_type').': '.($lead->stoneType?->name_en ?? $dash))
            ->line(__('site.lead_mail.project').': '.($lead->project?->title_en ?? $dash))
            ->line(__('site.lead_mail.message').':')
            ->line($lead->message ?? $dash)
            ->line(__('site.lead_mail.language').': '.strtoupper((string) ($lead->locale ?: $dash)))
            ->action(__('site.lead_mail.view_in_admin'), route('filament.admin.resources.leads.edit', $lead));

        if ($lead->email) {
            $message->replyTo($lead->email, $lead->name);
        }

        return $message;
    }
}

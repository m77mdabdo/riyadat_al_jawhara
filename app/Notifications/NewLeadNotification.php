<?php

namespace App\Notifications;

use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\Concerns\ReliableDelivery;
use App\Support\Phone;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Throwable;

/**
 * A new quote request from the website.
 *
 * Recipients: every admin user (mail + admin bell, in their own admin
 * language) and the company email from Settings (mail, in the company
 * language), de-duplicated. Queued with retries; a mail problem never
 * reaches the visitor.
 */
class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable, ReliableDelivery;

    public function __construct(public Lead $lead)
    {
        $this->configureDelivery();
    }

    public static function dispatchFor(Lead $lead): void
    {
        try {
            $admins = User::query()
                ->whereHas('roles', fn ($query) => $query->where('name', UserRole::Admin->value))
                ->get();

            if ($admins->isNotEmpty()) {
                NotificationFacade::send($admins, new self($lead));
            }

            $companyEmail = Setting::current()->email;

            if (filled($companyEmail) && ! $admins->contains(fn (User $user) => strcasecmp($user->email, $companyEmail) === 0)) {
                NotificationFacade::route('mail', $companyEmail)
                    ->notify((new self($lead))->locale(config('site.notifications.company_locale')));
            }
        } catch (Throwable $e) {
            // The lead is already saved; a notification problem must never
            // turn a successful submission into an error page.
            report($e);
        }
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead->loadMissing(['service', 'stoneType', 'project']);
        $whatsapp = Phone::whatsappDigits($lead->phone);
        $validEmail = $lead->email && filter_var($lead->email, FILTER_VALIDATE_EMAIL) && ! preg_match('/[\r\n]/', $lead->email.$lead->name);

        $message = (new MailMessage)
            ->subject($this->subject())
            ->markdown('mail.leads.new', [
                'preheader' => __('site.mail.lead.preheader', ['name' => $lead->name]),
                'lead' => $lead,
                'rows' => $this->rows(),
                'contextUrl' => $this->contextUrl(),
                'whatsappUrl' => $whatsapp ? 'https://wa.me/'.$whatsapp.'?text='.rawurlencode(__('site.mail.lead.whatsapp_greeting', ['name' => $lead->name])) : null,
                'telUrl' => Phone::telHref($lead->phone),
                'mailtoUrl' => $validEmail ? 'mailto:'.$lead->email : null,
            ]);

        // Plain summary lines (added before the action so they are the intro).
        foreach ($this->summaryLines() as $line) {
            $message->line($line);
        }
        $message->action(__('site.mail.lead.open_admin'), route('filament.admin.resources.leads.view', $lead));

        // Only a strictly valid address may reach a mail header (defence in
        // depth against header injection; see StoreLeadRequest).
        if ($validEmail) {
            $message->replyTo($lead->email, $lead->name);
        }

        return $message;
    }

    /**
     * Filament database notification (the admin bell).
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('site.mail.lead.bell_title', ['name' => $this->lead->name]))
            ->body(implode(' · ', array_filter([$this->lead->phone, $this->topic()])))
            ->icon('heroicon-o-inbox-arrow-down')
            ->actions([
                Action::make('open')
                    ->label(__('site.mail.lead.open_admin'))
                    ->url(route('filament.admin.resources.leads.view', $this->lead))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    private function subject(): string
    {
        $topic = $this->topic();

        return $topic
            ? __('site.mail.lead.subject', ['name' => $this->lead->name, 'topic' => $topic])
            : __('site.mail.lead.subject_no_topic', ['name' => $this->lead->name]);
    }

    /** Stone, service or project the request is about (recipient's language). */
    private function topic(): ?string
    {
        $lead = $this->lead;

        return $lead->stoneType?->name ?: $lead->service?->name ?: $lead->project?->title ?: null;
    }

    /**
     * @return array<string, string|null>
     */
    private function rows(): array
    {
        $lead = $this->lead;

        return [
            __('site.mail.lead.name') => $lead->name,
            __('site.mail.lead.phone') => $lead->phone,
            __('site.mail.lead.email') => $lead->email,
            __('site.mail.lead.city') => $lead->city,
            __('site.mail.lead.service') => $lead->service?->name,
            __('site.mail.lead.stone_type') => $lead->stoneType?->name,
            __('site.mail.lead.project') => $lead->project?->title,
            __('site.mail.lead.source_page') => $lead->source_url,
            __('site.mail.lead.language') => $lead->locale ? strtoupper($lead->locale) : null,
            __('site.mail.lead.received') => $lead->created_at?->timezone(config('site.notifications.timezone'))->format('Y-m-d H:i'),
        ];
    }

    /**
     * @return list<string>
     */
    private function summaryLines(): array
    {
        $lines = [];
        foreach ($this->rows() as $label => $value) {
            if (filled($value)) {
                $lines[] = "{$label}: {$value}";
            }
        }
        if (filled($this->lead->message)) {
            $lines[] = __('site.mail.lead.message').': '.$this->lead->message;
        }

        return $lines;
    }

    /** Public page of the service, stone or project the visitor asked about. */
    private function contextUrl(): ?string
    {
        $lead = $this->lead;
        $locale = app()->getLocale();

        return match (true) {
            $lead->stoneType !== null => route('stones.show', ['locale' => $locale, 'stoneType' => $lead->stoneType]),
            $lead->service !== null => route('services.show', ['locale' => $locale, 'service' => $lead->service]),
            $lead->project !== null => route('projects.show', ['locale' => $locale, 'project' => $lead->project]),
            default => null,
        };
    }
}

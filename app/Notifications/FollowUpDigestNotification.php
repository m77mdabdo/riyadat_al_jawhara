<?php

namespace App\Notifications;

use App\Models\Lead;
use App\Notifications\Concerns\ReliableDelivery;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Daily list of leads whose follow-up date has come (leads:follow-up-digest).
 */
class FollowUpDigestNotification extends Notification implements ShouldQueue
{
    use Queueable, ReliableDelivery;

    /**
     * @param  Collection<int, Lead>  $leads
     */
    public function __construct(public Collection $leads)
    {
        $this->configureDelivery();
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->leads->count();

        return (new MailMessage)
            ->subject(__('site.mail.digest.subject', ['count' => $count]))
            ->markdown('mail.leads.digest', [
                'preheader' => __('site.mail.digest.preheader', ['count' => $count]),
                'rows' => $this->leads->mapWithKeys(fn (Lead $lead) => [
                    $lead->name.' #'.$lead->id => __('site.mail.digest.due', ['date' => $lead->follow_up_at?->format('Y-m-d')]).' · '.$lead->phone,
                ])->all(),
            ])
            ->action(__('site.mail.digest.open_list'), $this->listUrl());
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('site.mail.digest.bell_title', ['count' => $this->leads->count()]))
            ->body($this->leads->take(5)->pluck('name')->implode(', '))
            ->icon('heroicon-o-clock')
            ->warning()
            ->actions([
                Action::make('open')->label(__('site.mail.digest.open_list'))->url($this->listUrl())->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    private function listUrl(): string
    {
        return route('filament.admin.resources.leads.index', ['tableFilters[follow_up_due][isActive]' => true]);
    }
}

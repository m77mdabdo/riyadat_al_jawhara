<?php

namespace App\Notifications;

use App\Filament\Resources\RoleResource;
use App\Models\User;
use App\Notifications\Mail\StaffAccessMail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Invitation to the admin panel, or a new access link for an existing team
 * member. Sent immediately (not queued) so the admin who pressed the button
 * learns at once whether it went out. Written in the recipient's admin language.
 */
class StaffAccessNotification extends Notification
{
    public function __construct(public string $url, public bool $invitation = true) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $user = $notifiable instanceof User ? $notifiable : null;
        $roles = $user?->roles->pluck('name')->map(fn (string $name) => RoleResource::roleLabel($name))->join('، ') ?: '—';

        return StaffAccessMail::build(
            $this->url,
            $user->name ?? 'Nasser',
            $user->email ?? 'team@example.com',
            $roles,
            $this->invitation,
        );
    }
}

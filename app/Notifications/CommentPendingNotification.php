<?php

namespace App\Notifications;

use App\Enums\CommentStatus;
use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\Concerns\ReliableDelivery;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Throwable;

/**
 * A visitor comment is waiting for moderation. Admin bell for admins and
 * editors; email too when Settings → comment_mail_notifications is on.
 */
class CommentPendingNotification extends Notification implements ShouldQueue
{
    use Queueable, ReliableDelivery;

    public function __construct(public Comment $comment)
    {
        $this->configureDelivery();
    }

    public static function dispatchFor(Comment $comment): void
    {
        if ($comment->status !== CommentStatus::Pending->value) {
            return; // spam (honeypot) is never announced
        }

        try {
            $staff = User::query()->whereHas('roles', fn ($q) => $q->whereIn('name', UserRole::values()))->get();

            if ($staff->isNotEmpty()) {
                NotificationFacade::send($staff, new self($comment));
            }
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return Setting::current()->comment_mail_notifications ? ['database', 'mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $post = $this->comment->post->title ?? '';

        return (new MailMessage)
            ->subject(__('site.mail.comment.subject', ['post' => $post]))
            ->markdown('mail.comments.pending', [
                'preheader' => __('site.mail.comment.preheader', ['name' => $this->comment->name, 'post' => $post]),
                'intro' => __('site.mail.comment.intro', ['name' => $this->comment->name, 'post' => $post]),
            ])
            ->action(__('site.mail.comment.moderate'), $this->moderationUrl());
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title(__('site.mail.comment.bell_title'))
            ->body(__('site.mail.comment.intro', ['name' => $this->comment->name, 'post' => $this->comment->post->title ?? '']))
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->actions([
                Action::make('moderate')->label(__('site.mail.comment.moderate'))->url($this->moderationUrl())->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    private function moderationUrl(): string
    {
        return route('filament.admin.resources.comments.index', ['tableFilters[status][value]' => CommentStatus::Pending->value]);
    }
}

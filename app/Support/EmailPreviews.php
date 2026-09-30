<?php

namespace App\Support;

use App\Models\Comment;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Service;
use App\Models\StoneType;
use App\Notifications\CommentPendingNotification;
use App\Notifications\FollowUpDigestNotification;
use App\Notifications\LeadAutoReplyNotification;
use App\Notifications\Mail\PasswordResetMail;
use App\Notifications\NewLeadNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use InvalidArgumentException;

/**
 * Every email the site sends, rendered with sample data (nothing is read from
 * or written to the database). Used by the admin "Email previews" page and
 * app:mail-test.
 */
class EmailPreviews
{
    public const TEMPLATES = ['lead_new', 'lead_auto_reply', 'follow_up_digest', 'comment_pending', 'password_reset'];

    /**
     * @return array<string, string> key => label
     */
    public static function options(): array
    {
        return [
            'lead_new' => 'New lead (team)',
            'lead_auto_reply' => 'Auto-reply (visitor)',
            'follow_up_digest' => 'Follow-up digest (team)',
            'comment_pending' => 'Comment awaiting review (team)',
            'password_reset' => 'Admin password reset',
        ];
    }

    public function html(string $template, string $locale): string
    {
        return $this->withLocale($locale, fn () => (string) $this->message($template)->render());
    }

    public function text(string $template, string $locale): string
    {
        return $this->withLocale($locale, function () use ($template) {
            $message = $this->message($template);

            return (string) app(Markdown::class)->renderText($message->markdown, $message->data());
        });
    }

    public function subject(string $template, string $locale): string
    {
        return $this->withLocale($locale, fn () => (string) $this->message($template)->subject);
    }

    /**
     * Sends one sample email. Only to the configured test address
     * (MAIL_TEST_RECIPIENT); anything else is refused.
     */
    public function sendTest(string $template, string $locale, string $to): void
    {
        $allowed = (string) config('mail.test_recipient');

        if ($allowed === '' || strcasecmp(trim($to), $allowed) !== 0) {
            throw new InvalidArgumentException('Test emails can only be sent to MAIL_TEST_RECIPIENT.');
        }

        NotificationFacade::route('mail', $allowed)->notifyNow($this->notification($template)->locale($locale));
    }

    private function message(string $template): MailMessage
    {
        $notification = $this->notification($template);

        if (! method_exists($notification, 'toMail')) {
            throw new InvalidArgumentException("Template [{$template}] has no email.");
        }

        return $notification->toMail(new AnonymousNotifiable);
    }

    private function notification(string $template): Notification
    {
        $lead = $this->sampleLead();

        return match ($template) {
            'lead_new' => new NewLeadNotification($lead),
            'lead_auto_reply' => new LeadAutoReplyNotification($lead),
            'follow_up_digest' => new FollowUpDigestNotification(new Collection([$lead, $this->sampleLead(1025, 'Faisal Al-Otaibi')])),
            'comment_pending' => new CommentPendingNotification($this->sampleComment()),
            'password_reset' => new class extends Notification
            {
                public function toMail(object $notifiable): MailMessage
                {
                    return PasswordResetMail::build(url('/admin/password-reset/reset?token=sample-token'));
                }

                /**
                 * @return list<string>
                 */
                public function via(object $notifiable): array
                {
                    return ['mail'];
                }
            },
            default => throw new InvalidArgumentException("Unknown email template [{$template}]."),
        };
    }

    private function sampleLead(int $id = 1024, string $name = 'Nasser Al-Dosari'): Lead
    {
        $lead = new Lead([
            'name' => $name,
            'phone' => '0551234567',
            'email' => 'customer@example.com',
            'city' => 'Riyadh',
            'message' => "We need about 80 m² of Carrara marble for a villa lobby.\nCan you share prices and delivery time?",
            'locale' => 'ar',
            'source_url' => url('/ar/stones/carrara-marble'),
        ]);
        $lead->id = $id;
        $lead->created_at = now();
        $lead->follow_up_at = today();
        $lead->setRelation('stoneType', new StoneType(['name_ar' => 'رخام كرارا', 'name_en' => 'Carrara marble', 'slug' => 'carrara-marble']));
        $lead->setRelation('service', new Service(['name_ar' => 'تركيب الرخام', 'name_en' => 'Marble installation', 'slug' => 'marble-installation']));
        $lead->setRelation('project', null);

        return $lead;
    }

    private function sampleComment(): Comment
    {
        $comment = new Comment(['name' => 'Sara', 'body' => 'Great guide!']);
        $comment->setRelation('post', new Post(['title_ar' => 'كيف تختار الرخام المناسب', 'title_en' => 'How to choose the right marble', 'slug' => 'how-to-choose-marble']));

        return $comment;
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function withLocale(string $locale, callable $callback): mixed
    {
        $previous = app()->getLocale();
        app()->setLocale($locale);

        try {
            return $callback();
        } finally {
            app()->setLocale($previous);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Enums\CommentStatus;
use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CommentPendingNotification;
use App\Notifications\FollowUpDigestNotification;
use App\Notifications\LeadAutoReplyNotification;
use App\Notifications\NewLeadNotification;
use App\Support\EmailPreviews;
use Filament\Notifications\Auth\ResetPassword;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role, string $locale = 'en'): User
    {
        Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);

        return tap(User::factory()->create(['locale' => $locale]))->assignRole($role->value);
    }

    private function reachableCompany(array $extra = []): void
    {
        Setting::editable()->update(['phone' => '+966 11 000 0000', 'whatsapp' => '966500000000'] + $extra);
    }

    private function submit(array $overrides = []): void
    {
        $this->post(route('contact.store'), array_merge([
            'name' => 'Customer', 'phone' => '0550000000', 'email' => 'visitor@example.test',
            'message' => 'Visit http://spam.example and buy', 'consent' => '1',
        ], $overrides));
    }

    public function test_new_lead_goes_to_admins_in_their_language_with_mail_and_bell(): void
    {
        Notification::fake();
        $arabicAdmin = $this->user(UserRole::Admin, 'ar');
        $editor = $this->user(UserRole::Editor);

        $this->submit();

        Notification::assertSentTo($arabicAdmin, NewLeadNotification::class, function ($n, array $channels, $notifiable, string $locale) {
            return $channels === ['mail', 'database'] && $locale === 'ar';
        });
        Notification::assertNotSentTo($editor, NewLeadNotification::class);
    }

    public function test_company_inbox_gets_the_company_language(): void
    {
        Notification::fake();
        Setting::editable()->update(['email' => 'office@example.test']);

        $this->submit();

        Notification::assertSentOnDemand(NewLeadNotification::class, function ($n, array $channels, AnonymousNotifiable $notifiable, string $locale) {
            return $notifiable->routes['mail'] === 'office@example.test' && $locale === config('site.notifications.company_locale') && $channels === ['mail'];
        });
    }

    public function test_auto_reply_goes_to_the_visitor_in_their_language(): void
    {
        Notification::fake();
        $this->reachableCompany();
        $this->useLocale('ar');

        $this->submit();

        Notification::assertSentOnDemand(LeadAutoReplyNotification::class, function ($n, array $channels, AnonymousNotifiable $notifiable, string $locale) {
            return $notifiable->routes['mail'] === 'visitor@example.test' && $locale === 'ar';
        });
    }

    public function test_auto_reply_respects_the_toggle_email_and_contact_details(): void
    {
        Notification::fake();

        $this->submit(); // Settings have no phone/WhatsApp yet
        $this->reachableCompany(['lead_auto_reply' => false]);
        $this->submit();
        Setting::editable()->update(['lead_auto_reply' => true]);
        $this->submit(['email' => '']);

        Notification::assertSentOnDemandTimes(LeadAutoReplyNotification::class, 0);
    }

    public function test_auto_reply_never_echoes_the_visitors_free_text(): void
    {
        $this->reachableCompany();
        $lead = Lead::factory()->create(['name' => 'Sara', 'message' => 'Visit http://spam.example now', 'city' => 'SPAMCITY', 'email' => 'v@example.test']);

        $html = (string) (new LeadAutoReplyNotification($lead))->toMail(new AnonymousNotifiable)->render();

        $this->assertStringContainsString('Sara', $html);
        $this->assertStringNotContainsString('spam.example', $html);
        $this->assertStringNotContainsString('SPAMCITY', $html);
    }

    public function test_link_like_names_are_not_echoed(): void
    {
        foreach (['http://evil.example', 'www.evil.com', 'buy at evil.com/now', 'a@b.c', str_repeat('x', 61)] as $name) {
            $this->assertNull(LeadAutoReplyNotification::safeName($name), $name);
        }
        $this->assertSame('محمد العتيبي', LeadAutoReplyNotification::safeName('  محمد   العتيبي '));
    }

    public function test_notifications_are_queued_with_retries_and_a_failure_handler(): void
    {
        $lead = Lead::factory()->create();

        foreach ([new NewLeadNotification($lead), new LeadAutoReplyNotification($lead), new CommentPendingNotification(Comment::factory()->create()), new FollowUpDigestNotification(Lead::all())] as $n) {
            $this->assertInstanceOf(ShouldQueue::class, $n);
            $this->assertSame(3, $n->tries);
            $this->assertSame([60, 300, 900], $n->backoff());
            $this->assertTrue(method_exists($n, 'failed'));
        }
    }

    public function test_digest_only_sends_when_something_is_due(): void
    {
        Notification::fake();
        $admin = $this->user(UserRole::Admin);
        $editor = $this->user(UserRole::Editor);

        $this->artisan('leads:follow-up-digest')->assertSuccessful();
        Notification::assertNothingSent();

        Lead::factory()->create(['follow_up_at' => today()->subDay(), 'assigned_to' => $editor->id]);
        Lead::factory()->create(['follow_up_at' => today()]); // unassigned → admins
        Lead::factory()->create(['follow_up_at' => today()->addDay()]); // not due

        $this->artisan('leads:follow-up-digest')->assertSuccessful();

        Notification::assertSentTo($editor, FollowUpDigestNotification::class, fn ($n) => $n->leads->count() === 1);
        Notification::assertSentTo($admin, FollowUpDigestNotification::class, fn ($n) => $n->leads->count() === 1);
    }

    public function test_digest_is_scheduled_at_eight_riyadh_time(): void
    {
        $event = collect(app(Schedule::class)->events())->first(fn ($e) => str_contains($e->command, 'leads:follow-up-digest'));

        $this->assertNotNull($event);
        $this->assertSame('0 8 * * *', $event->expression);
        $this->assertSame('Asia/Riyadh', $event->timezone);
    }

    public function test_pending_comments_notify_staff_by_bell_and_optionally_mail(): void
    {
        Notification::fake();
        $admin = $this->user(UserRole::Admin);
        $editor = $this->user(UserRole::Editor);
        $post = Post::factory()->create();

        $this->post(route('blog.comments.store', $post), ['name' => 'N', 'email' => 'n@example.test', 'body' => 'Nice']);
        Notification::assertSentTo([$admin, $editor], CommentPendingNotification::class, fn ($n, array $channels) => $channels === ['database']);

        Setting::editable()->update(['comment_mail_notifications' => true]);
        Setting::flushCache();
        $this->assertSame(['database', 'mail'], (new CommentPendingNotification(Comment::factory()->create()))->via($admin));
    }

    public function test_spam_comments_are_not_announced(): void
    {
        Notification::fake();
        $this->user(UserRole::Admin);

        CommentPendingNotification::dispatchFor(Comment::factory()->create(['status' => CommentStatus::Spam->value]));

        Notification::assertNothingSent();
    }

    public function test_arabic_mail_is_rtl_with_logo_fallback_buttons_and_preheader(): void
    {
        $lead = Lead::factory()->create(['name' => 'Nasser', 'phone' => '0551112222', 'email' => 'n@example.test', 'locale' => 'ar', 'message' => 'نحتاج رخام']);
        app()->setLocale('ar');

        $html = (string) (new NewLeadNotification($lead))->toMail(new AnonymousNotifiable)->render();

        $this->assertStringContainsString('dir="rtl"', $html);
        $this->assertStringContainsString('lang="ar"', $html);
        $this->assertStringContainsString('alt="'.__('site.meta.site_name').'"', $html, 'logo alt text shows when images are blocked');
        $this->assertStringContainsString('class="preheader"', $html);
        $this->assertStringContainsString('https://wa.me/966551112222', $html);
        $this->assertStringContainsString('tel:0551112222', $html);
        $this->assertStringContainsString('mailto:n@example.test', $html);
        $this->assertStringContainsString('bgcolor="#C9A45A"', $html, 'bulletproof button');
        $this->assertStringContainsString('نحتاج رخام', $html);
        $this->assertStringContainsString('Tahoma', $html);

        app()->setLocale('en');
        $en = (string) (new NewLeadNotification($lead))->toMail(new AnonymousNotifiable)->render();
        $this->assertStringContainsString('dir="ltr"', $en);
    }

    public function test_email_previews_page_is_admin_only_and_renders_every_template(): void
    {
        $this->actingAs($this->user(UserRole::Editor));
        $this->get('/admin/email-previews')->assertForbidden();

        $this->actingAs($this->user(UserRole::Admin));
        $this->get('/admin/email-previews')->assertOk()->assertSee('srcdoc=', false);

        $previews = app(EmailPreviews::class);
        foreach (EmailPreviews::TEMPLATES as $template) {
            foreach (['ar', 'en'] as $locale) {
                $html = $previews->html($template, $locale);
                $this->assertStringContainsString('dir="'.($locale === 'ar' ? 'rtl' : 'ltr').'"', $html, "{$template}/{$locale}");
                $this->assertNotSame('', trim($previews->text($template, $locale)));
                $this->assertNotSame('', $previews->subject($template, $locale));
            }
        }
    }

    public function test_test_emails_only_go_to_the_configured_address(): void
    {
        Notification::fake();
        config(['mail.test_recipient' => 'owner@example.test']);

        $this->artisan('app:mail-test', ['to' => 'someone-else@example.test'])->assertFailed();
        Notification::assertNothingSent();

        $this->expectException(\InvalidArgumentException::class);
        app(EmailPreviews::class)->sendTest('lead_new', 'ar', 'someone-else@example.test');
    }

    public function test_mail_test_sends_every_template_in_both_languages_to_the_test_address(): void
    {
        Notification::fake();
        config(['mail.test_recipient' => 'owner@example.test']);

        $this->artisan('app:mail-test', ['to' => 'owner@example.test'])
            ->expectsOutput('sent password_reset (ar)')
            ->expectsOutput('sent password_reset (en)')
            ->assertSuccessful();

        // Each template in both languages, all to the test address.
        foreach ([NewLeadNotification::class, LeadAutoReplyNotification::class, FollowUpDigestNotification::class, CommentPendingNotification::class] as $class) {
            Notification::assertSentOnDemandTimes($class, 2);
            Notification::assertSentOnDemand($class, fn ($n, $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'owner@example.test');
        }
    }

    public function test_admin_password_reset_uses_the_branded_localized_content(): void
    {
        $user = $this->user(UserRole::Admin, 'ar');
        app()->setLocale('ar');

        $mail = (new ResetPassword('t'))->toMail($user);

        $this->assertSame(__('site.mail.reset.subject'), $mail->subject);
        $this->assertStringContainsString('password-reset', $mail->actionUrl);
        $this->assertStringContainsString('dir="rtl"', (string) $mail->render());
    }
}

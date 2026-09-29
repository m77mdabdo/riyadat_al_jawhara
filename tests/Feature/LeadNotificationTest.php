<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewLeadNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeadNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function submit(array $overrides = []): void
    {
        $this->from(route('contact'))->post(route('contact.store'), array_merge([
            'name' => 'Customer',
            'phone' => '+966 55 000 0000',
            'email' => 'customer@example.test',
            'message' => 'Quote please',
            'consent' => '1',
        ], $overrides));
    }

    private function admin(string $email = 'admin@example.test'): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        return tap(User::factory()->create(['email' => $email]))->assignRole('admin');
    }

    public function test_it_notifies_admins_and_the_company_inbox(): void
    {
        Notification::fake();
        $admin = $this->admin();
        Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor = tap(User::factory()->create())->assignRole('editor');
        Setting::current()->update(['email' => 'info@company.test']);

        $this->submit();

        $lead = Lead::firstOrFail();
        Notification::assertSentTo($admin, NewLeadNotification::class, fn ($n) => $n->lead->is($lead));
        Notification::assertNotSentTo($editor, NewLeadNotification::class);
        Notification::assertSentOnDemand(
            NewLeadNotification::class,
            fn ($n, array $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'info@company.test'
        );
    }

    public function test_company_inbox_is_not_emailed_twice_when_it_is_an_admin(): void
    {
        Notification::fake();
        $this->admin('info@company.test');
        Setting::current()->update(['email' => 'INFO@company.test']);

        $this->submit();

        Notification::assertSentTimes(NewLeadNotification::class, 1);
    }

    public function test_honeypot_and_invalid_submissions_send_nothing(): void
    {
        Notification::fake();
        $this->admin();

        $this->submit(['website' => 'spam']);
        $this->submit(['name' => '']);

        Notification::assertNothingSent();
    }

    public function test_no_recipients_is_not_an_error(): void
    {
        Notification::fake();

        $this->submit();

        $this->assertSame(1, Lead::count());
        Notification::assertNothingSent();
    }

    public function test_notification_is_queued_mail(): void
    {
        $lead = Lead::create(['name' => 'X', 'phone' => '1', 'message' => 'm', 'status' => 'new', 'source' => 'website']);
        $notification = new NewLeadNotification($lead);

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertSame(['mail'], $notification->via(new AnonymousNotifiable));
    }

    public function test_mail_contains_the_lead_details_and_admin_link(): void
    {
        $lead = Lead::create([
            'name' => 'Nasser', 'phone' => '+966 55 111', 'email' => 'n@example.test',
            'city' => 'Riyadh', 'message' => 'Need 80 m2 Carrara', 'status' => 'new', 'source' => 'website', 'locale' => 'ar',
        ]);

        $mail = (new NewLeadNotification($lead))->toMail(new AnonymousNotifiable);
        $text = implode("\n", $mail->introLines);

        $this->assertStringContainsString('Nasser', $mail->subject);
        $this->assertStringContainsString('+966 55 111', $text);
        $this->assertStringContainsString('Need 80 m2 Carrara', $text);
        $this->assertStringContainsString('/admin/leads/'.$lead->id, $mail->actionUrl);
        $this->assertSame([['n@example.test', 'Nasser']], $mail->replyTo);
    }

    public function test_queued_mail_is_actually_rendered_and_sent(): void
    {
        // Real pipeline (sync queue + array transport, no files written):
        // proves the notification renders and reaches a mailer.
        config(['queue.default' => 'sync', 'mail.default' => 'array']);
        $this->admin();

        $this->submit();

        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Customer', $messages[0]->getOriginalMessage()->getSubject());
    }
}

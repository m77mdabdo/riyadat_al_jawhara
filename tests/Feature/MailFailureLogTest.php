<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Notifications\NewLeadNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Tests\TestCase;

/**
 * Logs of failed emails never contain the recipient's address.
 */
class MailFailureLogTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<MessageLogged> */
    private array $logged = [];

    protected function setUp(): void
    {
        parent::setUp();

        Event::listen(MessageLogged::class, function (MessageLogged $event) {
            $this->logged[] = $event;
        });
    }

    private function logText(): string
    {
        return collect($this->logged)
            ->map(fn (MessageLogged $e) => $e->message.' '.json_encode($e->context, JSON_UNESCAPED_SLASHES))
            ->join("\n");
    }

    public function test_a_reported_mail_server_error_is_logged_without_the_address(): void
    {
        report(new TransportException('Expected response code "250" but got code "550", with message "550 5.1.1 <visitor.name@client.example>: Recipient address rejected".'));
        report(new RfcComplianceException('Email "staff@company.example" does not comply with addr-spec of RFC 2822.'));

        $text = $this->logText();
        $this->assertCount(2, $this->logged);
        $this->assertStringNotContainsString('visitor.name@client.example', $text);
        $this->assertStringNotContainsString('staff@company.example', $text);
        $this->assertStringContainsString('Recipient address rejected', $text);
        $this->assertStringContainsString('[address]', $text);
    }

    public function test_a_notification_that_finally_fails_is_logged_without_the_address(): void
    {
        $lead = Lead::factory()->create();

        (new NewLeadNotification($lead))->failed(new TransportException('550 <owner@company.example>: mailbox unavailable'));

        $text = $this->logText();
        $this->assertStringContainsString('Notification could not be delivered', $text);
        $this->assertSame(NewLeadNotification::class, $this->logged[0]->context['notification']);
        $this->assertStringNotContainsString('owner@company.example', $text);
    }
}

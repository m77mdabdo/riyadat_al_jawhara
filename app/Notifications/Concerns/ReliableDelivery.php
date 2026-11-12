<?php

namespace App\Notifications\Concerns;

use App\Support\MailFailureLog;
use Throwable;

/**
 * Queued delivery with retries (config/site.php → notifications). A final
 * failure is logged; it never surfaces to the visitor who triggered it.
 */
trait ReliableDelivery
{
    public int $tries = 3;

    protected function configureDelivery(): void
    {
        $this->tries = (int) config('site.notifications.tries', 3);
        $this->afterCommit();
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return array_map('intval', (array) config('site.notifications.backoff', [60, 300, 900]));
    }

    public function failed(Throwable $exception): void
    {
        // Never with the recipient's address (MailFailureLog).
        MailFailureLog::write('Notification could not be delivered', $exception, [
            'notification' => static::class,
        ]);
    }
}

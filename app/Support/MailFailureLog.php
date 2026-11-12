<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Failed emails are logged without the recipient: mail servers put the
 * rejected address in their error text ("550 <name@example.com>: …"), and the
 * logs must not collect the addresses of visitors and staff.
 */
class MailFailureLog
{
    public const ADDRESS = '[address]';

    public static function redact(string $text): string
    {
        return preg_replace('/[^\s<>"\'()\[\],;:]+@[^\s<>"\'()\[\],;:]+/u', self::ADDRESS, $text) ?? '[message removed]';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function write(string $message, Throwable $exception, array $context = []): void
    {
        Log::error($message, $context + [
            'error' => $exception::class.': '.self::redact($exception->getMessage()),
            'at' => basename($exception->getFile()).':'.$exception->getLine(),
        ]);
    }
}

<?php

namespace App\Console\Commands;

use App\Support\EmailPreviews;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

/**
 * Renders every email template with sample data in Arabic and English and
 * sends them to the test address. --save writes the HTML files instead of
 * (or as well as) sending.
 */
class MailTest extends Command
{
    protected $signature = 'app:mail-test
                            {to? : Must equal MAIL_TEST_RECIPIENT}
                            {--save= : Directory to write rendered HTML/text previews to}
                            {--no-send : Only render (with --save)}';

    protected $description = 'Send (or save) every email template with sample data, in AR and EN';

    public function handle(EmailPreviews $previews): int
    {
        if ($dir = $this->option('save')) {
            File::ensureDirectoryExists($dir);
            foreach (EmailPreviews::TEMPLATES as $template) {
                foreach (['ar', 'en'] as $locale) {
                    File::put("{$dir}/{$template}-{$locale}.html", $previews->html($template, $locale));
                    File::put("{$dir}/{$template}-{$locale}.txt", $previews->text($template, $locale));
                }
            }
            $this->info("Previews written to {$dir}");
        }

        if ($this->option('no-send')) {
            return self::SUCCESS;
        }

        $to = (string) $this->argument('to');
        if ($to === '' || blank(config('mail.test_recipient')) || strcasecmp($to, (string) config('mail.test_recipient')) !== 0) {
            $this->error('Refusing to send: the address must equal MAIL_TEST_RECIPIENT (set it in .env).');

            return self::FAILURE;
        }

        try {
            foreach (EmailPreviews::TEMPLATES as $template) {
                foreach (['ar', 'en'] as $locale) {
                    $previews->sendTest($template, $locale, $to);
                    $this->line("sent {$template} ({$locale})");
                }
            }
        } catch (Throwable $e) {
            // Class only: messages from mail transports can contain server details.
            $this->error('Sending failed: '.$e::class);

            return self::FAILURE;
        }

        $this->info('All test emails sent.');

        return self::SUCCESS;
    }
}

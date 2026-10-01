<?php

namespace App\Support;

use App\Models\Setting;

/**
 * When the daily follow-up digest is sent (HH:MM, Riyadh time).
 */
class FollowUpDigestTime
{
    public static function get(): string
    {
        // The schedule is read by every artisan command, including before the
        // database exists: fall back to the config value on any problem.
        $time = rescue(fn () => Setting::current()->option('notification_options', 'digest_time'), null, false);

        return is_string($time) && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) ? $time : (string) config('site.notifications.digest_time');
    }
}

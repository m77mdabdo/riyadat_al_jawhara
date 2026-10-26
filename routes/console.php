<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Privacy: drop IP/user agent from old comments and leads (config/privacy.php).
Schedule::command('privacy:prune')->dailyAt('03:15')->withoutOverlapping();

// Admin activity log: keep 365 days (config activitylog.delete_records_older_than_days).
Schedule::command('activitylog:clean')->weeklyOn(1, '03:30')->withoutOverlapping();

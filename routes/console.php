<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Privacy: drop IP/user agent from old comments and leads (config/privacy.php).
Schedule::command('privacy:prune')->dailyAt('03:15')->withoutOverlapping();

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule payments sync to run every minute (daemon mode is better for every second)
Schedule::command('payments:sync')->everyMinute();
Schedule::command('app:sync-bills-from-api')->everyFiveMinutes();
// Run payout sync every second by looping 60 times per minute
Schedule::call(function () {
    for ($i = 0; $i < 60; $i++) {
        Artisan::call('app:sync-click-pesa-payouts');
        if ($i < 59) {
            usleep(1000000); // Sleep 1 second
        }
    }
})->everyMinute()->name('Payout Sync Every Second')->withoutOverlapping();
Schedule::command('app:cleanup-expired-sessions')->everyMinute();


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
Schedule::command('app:sync-click-pesa-payouts')->everyFiveMinutes();


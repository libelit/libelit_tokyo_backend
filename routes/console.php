<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// XRPL Blockchain Audit Trail - Verify pending transactions every 5 minutes
Schedule::command('xrpl:verify-pending-transactions')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('subscriptions:send-reminders')->dailyAt('08:00');
Schedule::command('subscriptions:expire-unrecharged')->dailyAt('08:15');
Schedule::command('accounts:reassign-expiring')->dailyAt('08:30');

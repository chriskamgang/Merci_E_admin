<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Commands (Laravel 11/12)
|--------------------------------------------------------------------------
*/

Schedule::command('drivers:totrip')
    ->everyMinute();

Schedule::command('assign_drivers:for_regular_rides')
    ->everyMinute();

Schedule::command('assign_drivers:for_schedule_rides')
    ->everyFiveMinutes();

Schedule::command('offline:drivers')
    ->everyFiveMinutes();

Schedule::command('notify:document:expires')
    ->daily();

Schedule::command('expire:subscription')
    ->everyFiveMinutes();

Schedule::command('clear:otp')
    ->everyFiveMinutes();

Schedule::command('cancel:request')
    ->everyMinute();

Schedule::command('promotion:deactivate-expired')
    ->everyMinute();

// KPay: settle pending mobile-money deposits / payouts (credits & refunds are
// idempotent — see App\Services\WalletService). Requires a running queue worker
// (QUEUE_CONNECTION=database): the scheduler only dispatches the job.
Schedule::job(new \App\Jobs\KPayPollPendingTransactions)
    ->everyMinute()
    ->withoutOverlapping();

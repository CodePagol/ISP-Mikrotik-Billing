<?php

use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\ScheduledTasksController;
use App\Livewire\MikrotikSync;
use App\Models\MainSiteData;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

$timezone = config('app.timezone') ?: 'Asia/Dhaka';

Schedule::call(function () {
    app(ScheduledTasksController::class)->createMonthlyBill();
})->lastDayOfMonth('23:45')->timezone($timezone);

Schedule::call(function () {
    app(ScheduledTasksController::class)->allCustomersMonthlyBillSMS();
})->monthlyOn(1, '10:00')->timezone($timezone);

Schedule::call(function () {
    app(ScheduledTasksController::class)->createAlert();
})->dailyAt('08:00')->timezone($timezone);

Schedule::call(function () {
    app(ScheduledTasksController::class)->userDisable();
})->dailyAt('08:30')->timezone($timezone);

Schedule::call(function () {
    app(MikrotikSync::class)->allSync();
})->daily()->timezone($timezone);

Schedule::command('app:poll-router-logs')->everyMinute();

Schedule::call(function () {
    $days = (int) MainSiteData::getValue('log_retention_days', 30);
    app(MikrotikController::class)->pruneOldLogs($days);
})->dailyAt('04:00');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule::call(function () {
//     Log::info('Hello World', [
//         'time' => Carbon::now(),
//         'timezone' => config('app.timezone'),
//     ]);
// })->everyMinute();

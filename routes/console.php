<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('sla:check')->hourly()->withoutOverlapping();
Schedule::command('tickets:reminders')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('db:backup')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('seo:indexnow')->dailyAt('02:45')->withoutOverlapping();

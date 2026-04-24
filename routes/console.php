<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('agri:forecast-pests')->dailyAt('04:00');
Schedule::command('agri:refresh-roadmaps')->weeklyOn(0, '02:00');

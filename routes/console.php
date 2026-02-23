<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic cleanup of stuck article generation jobs
// Runs every 5 minutes to quickly catch and fix any stuck jobs
Schedule::command('articles:fix-stuck-jobs --force')->everyFiveMinutes();

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Artisan::call('app:update-offline-users');
})->everyMinute();
Schedule::call(function () {
    Artisan::call('scraper:monitor');
})->everyTenSeconds();


<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Gecikmiş zimmetleri her 15 dakikada bir kontrol et
Schedule::command('loans:check-overdue')->everyFifteenMinutes();

// Her gece 23:55'te günlük raporu oluştur ve arşivle
Schedule::command('reports:generate-daily')->dailyAt('23:55');

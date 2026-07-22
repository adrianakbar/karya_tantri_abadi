<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backup Otomatis Database Koperasi - Setiap Minggu
Schedule::command('backup:run --only-db')
    ->weekly()
    ->mondays()
    ->at('02:00')
    ->name('backup-database-mingguan')
    ->description('Backup database koperasi setiap minggu')
    ->onSuccess(function () {
        Log::info('Backup database mingguan berhasil pada ' . now());
    })
    ->onFailure(function () {
        Log::error('Backup database mingguan gagal pada ' . now());
    });

// Cleanup backup lama - Setiap bulan  
Schedule::command('backup:clean')
    ->monthly()
    ->description('Cleanup backup files yang lebih dari 30 hari');

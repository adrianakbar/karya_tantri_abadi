<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MemberCardController;
use App\Http\Controllers\SavingsReceiptController;
use App\Http\Controllers\BackupDownloadController;
use Illuminate\Support\Facades\Route;

// Satu halaman login universal — deteksi role otomatis, redirect ke panel sesuai.
Route::get('/', fn () => redirect()->route('login'))->name('home');
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');

Route::middleware('auth')->group(function () {
    Route::get('/member/{user}/card/print', [MemberCardController::class, 'printSingle'])
        ->name('member.card.print');

    Route::get('/member/cards/print', [MemberCardController::class, 'printMultiple'])
        ->name('member.cards.print');

    Route::get('/savings/{transaction}/print', [SavingsReceiptController::class, 'print'])
        ->name('savings.print');

    Route::get('/download-backup/{fileName}', [BackupDownloadController::class, 'download'])
        ->name('backup.download');
});

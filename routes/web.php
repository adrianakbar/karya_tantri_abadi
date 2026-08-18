<?php

use App\Http\Controllers\MemberCardController;
use App\Http\Controllers\SavingsReceiptController;
use App\Http\Controllers\BackupDownloadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Home: landing pilihan role
Route::get('/', fn () => view('role-select'))->name('home');


Route::get('/login', fn () => view('role-select'))->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/member/{user}/card/print', [MemberCardController::class, 'printSingle'])
        ->name('member.card.print');

    Route::get('/member/cards/print', [MemberCardController::class, 'printMultiple'])
        ->name('member.cards.print');

    Route::get('/savings/{transaction}/print', [SavingsReceiptController::class, 'print'])
        ->name('savings.print');

    Route::get('/download-backup/{fileName}', [BackupDownloadController::class, 'download'])
        ->middleware('auth')
        ->name('backup.download');
});

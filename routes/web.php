<?php

use App\Http\Controllers\MemberCardController;
use App\Http\Controllers\SavingsReceiptController;
use App\Http\Controllers\BackupDownloadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Home route: redirect authenticated users to their role panel
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect('/auth/login');
    }

    $user = Auth::user();
    $role = $user->roles()->first();

    return match ($role?->name) {
        'admin', 'manager' => redirect('/admin'),
        'anggota' => redirect('/anggota'),
        'kasir', 'cashier', 'bendahara' => redirect('/kasir'),
        'spv', 'kepalayayasan', 'kepala_yayasan' => redirect('/spv'),
        default => redirect('/auth/login'),
    };
});

Route::get('/login', fn () => redirect('/auth/login'))->name('login');

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
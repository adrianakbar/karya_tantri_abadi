<?php

use App\Http\Controllers\Api\MemberApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [MemberApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [MemberApiController::class, 'logout']);
    Route::get('/profile', [MemberApiController::class, 'profile']);
    Route::get('/dashboard', [MemberApiController::class, 'dashboard']);
    Route::get('/savings', [MemberApiController::class, 'savings']);
    Route::get('/loans', [MemberApiController::class, 'loans']);
    Route::get('/purchases', [MemberApiController::class, 'purchases']);
    Route::get('/shu', [MemberApiController::class, 'shu']);
});


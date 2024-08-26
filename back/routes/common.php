<?php

use App\Http\Controllers\Common\{AuthController, LogController, TelegramBotController};
use App\Http\Controllers\Common\MangoController;
use Illuminate\Support\Facades\Route;

Route::post('telegram', TelegramBotController::class);

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('login', 'login');
    Route::post('verify-code', 'verifyCode');
});

Route::post('mango/events/{event}', MangoController::class)
    ->where('event', '[\D]+')
    ->name('mango');

Route::middleware(['auth:crm'])->group(function () {
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::get('user', 'user');
        Route::get('logout', 'logout');
    });
    Route::apiResource('logs', LogController::class)->only('store');
});

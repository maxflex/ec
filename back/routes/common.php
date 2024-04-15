<?php

use App\Http\Controllers\Crm\AuthController;
use App\Http\Controllers\Crm\TelegramController;
use Illuminate\Support\Facades\Route;



Route::post('telegram', [TelegramController::class, 'bot']);

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('login', 'login');
    Route::post('verify-code', 'verifyCode');
});
Route::middleware(['auth:crm'])->group(function () {
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::get('user', 'user');
        Route::get('logout', 'logout');
        Route::post('preview', 'preview');
    });
});

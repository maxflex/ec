<?php

use App\Http\Controllers\Pub\{RequestsController, SecurityController};
use Illuminate\Support\Facades\Route;

Route::prefix('requests')->controller(RequestsController::class)->group(function () {
    Route::post('/', 'store');
    Route::post('/verify', 'verify');
});

Route::prefix('security')->controller(SecurityController::class)->group(function () {
    Route::post('send-code', 'sendCode');
    Route::post('verify-code', 'verifyCode');
    Route::get('passes', 'getAllPasses');
    Route::post('passes', 'usePass');
    Route::get('history', 'history');
});

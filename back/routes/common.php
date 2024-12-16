<?php

use App\Http\Controllers\Common\{AuthController,
    EventController,
    ExamDateController,
    FileController,
    LogController,
    MangoController,
    TeethController,
    TelegramBotController,
    VacationController};
use Illuminate\Support\Facades\Route;

Route::post('telegram', TelegramBotController::class);

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('submit-phone', 'submitPhone');
    Route::post('verify-code', 'verifyCode');
});

Route::post('mango/events/{event}', MangoController::class)
    ->where('event', '[\D]+')
    ->name('mango');

Route::post('files', FileController::class);

Route::middleware(['auth:crm'])->group(function () {
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::get('user', 'user');
        Route::get('logout', 'logout');
    });
    Route::get('teeth', TeethController::class);
    Route::apiResource('logs', LogController::class)->only('store');
    Route::apiResources([
        'exam-dates' => ExamDateController::class,
        'vacations' => VacationController::class,
        'events' => EventController::class,
    ]);
});
<?php

use App\Http\Controllers\Common\AuthController;
use App\Http\Controllers\Common\AvailableYearsController;
use App\Http\Controllers\Common\EventController;
use App\Http\Controllers\Common\ExamDateController;
use App\Http\Controllers\Common\FileController;
use App\Http\Controllers\Common\LogController;
use App\Http\Controllers\Common\MangoController;
use App\Http\Controllers\Common\PhotoController;
use App\Http\Controllers\Common\TeethController;
use App\Http\Controllers\Common\TelegramBotController;
use App\Http\Controllers\Common\VacationController;
use Illuminate\Support\Facades\Route;

Route::post('telegram', TelegramBotController::class);

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('submit-phone', 'submitPhone');
    Route::post('verify-code', 'verifyCode');
    Route::post('magic-link', 'magicLink');
});

Route::post('mango/events/{event}', MangoController::class)
    ->where('event', '[\D]+')
    ->name('mango');

Route::post('files', FileController::class);

Route::get('available-years', AvailableYearsController::class);

Route::middleware(['auth:crm'])->group(function () {
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::get('user', 'user');
        Route::get('logout', 'logout');
    });
    Route::get('teeth', TeethController::class);
    Route::apiResource('logs', LogController::class)->only('store');
    Route::apiResource('photos', PhotoController::class)->only('store');
    Route::apiResources([
        'exam-dates' => ExamDateController::class,
        'vacations' => VacationController::class,
        'events' => EventController::class,
    ]);
});

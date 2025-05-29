<?php

use App\Http\Controllers\Pub\AuthController;
use App\Http\Controllers\Pub\MangoController;
use App\Http\Controllers\Pub\RequestsController;
use App\Http\Controllers\Pub\SecurityController;
use App\Http\Controllers\Pub\TeacherController;
use App\Http\Controllers\Pub\TelegramBotController;
use App\Http\Controllers\Pub\WebReviewController;
use Illuminate\Support\Facades\Route;

Route::post('telegram', TelegramBotController::class)->withoutMiddleware('throttle:pub');

Route::post('mango/events/{event}', MangoController::class)
    ->where('event', '[\D]+')
    ->name('mango')
    ->withoutMiddleware('throttle:pub');

Route::prefix('requests')->middleware('throttle:requests')->controller(RequestsController::class)->group(function () {
    Route::post('/', 'store');
    Route::post('/verify', 'verify');
});

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('submit-phone', 'submitPhone');
    Route::post('verify-code', 'verifyCode');
    Route::get('user', 'user')->middleware('auth:crm');
    Route::get('logout', 'logout')->middleware('auth:crm');

    route::post('via-telegram', 'viaTelegram');
    Route::post('via-magic-link', 'viaMagicLink');
});

Route::apiResource('/teachers', TeacherController::class)->only(['index', 'store']);

Route::prefix('security')->controller(SecurityController::class)->group(function () {
    Route::post('send-code', 'sendCode');
    Route::post('verify-code', 'verifyCode');
    Route::get('passes', 'getAllPasses');
    Route::post('passes', 'usePass');
    Route::get('history', 'history');
});

Route::apiResource('reviews', WebReviewController::class)->only('index', 'show');

<?php

use App\Http\Controllers\Pub\RequestsController;
use App\Http\Controllers\Pub\SecurityController;
use App\Http\Controllers\Pub\TeacherController;
use App\Http\Controllers\Pub\WebReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('requests')->controller(RequestsController::class)->group(function () {
    Route::post('/', 'store');
    Route::post('/verify', 'verify');
});

Route::apiResource('/teachers', TeacherController::class)->only('index');

Route::prefix('security')->controller(SecurityController::class)->group(function () {
    Route::post('send-code', 'sendCode');
    Route::post('verify-code', 'verifyCode');
    Route::get('passes', 'getAllPasses');
    Route::post('passes', 'usePass');
    Route::get('history', 'history');
});

Route::apiResource('reviews', WebReviewController::class)->only('index', 'show');

Route::get('macros', function () {
    return view('macros', [
        'inn' => '622709802712',
    ]);
});

<?php

use App\Http\Controllers\Client\{BillingController,
    ClientTestController,
    GroupController,
    LessonController,
    ReportController};
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:crm'])->group(function () {
    Route::controller(ClientTestController::class)->prefix('client-tests')->group(function () {
        Route::get('active', 'active');
        Route::post('start/{clientTest}', 'start');
        Route::post('finish', 'finish');
    });
    Route::apiResource('lessons', LessonController::class)->only(['index', 'show']);
    Route::get('billing', BillingController::class);
    Route::apiResource('groups', GroupController::class)->only('index');
    Route::apiResource('reports', ReportController::class)->only(['index', 'show']);
    Route::apiResource('client-tests', ClientTestController::class)->only('index', 'show');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\{
    BillingController,
    ClientTestController,
    GroupController,
    ReportController,
    ScheduleController
};

Route::middleware(['auth:crm'])->group(function () {
    Route::controller(ClientTestController::class)->prefix('client-tests')->group(function () {
        // Route::get('/', 'index');
        // Route::get('results/{clientTest}', 'results');
        // Route::get('/{clientTest}', 'show');
        Route::get('active', 'active');
        Route::post('start/{clientTest}', 'start');
        Route::post('finish', 'finish');
    });
    Route::get('billing', BillingController::class);
    Route::get('schedule/client/{client}', [ScheduleController::class, 'client']);
    Route::apiResource('groups', GroupController::class)->only('index');
    Route::apiResource('reports', ReportController::class)->only(['index', 'show']);
    Route::apiResource('client-tests', ClientTestController::class)->only('index', 'show');
});

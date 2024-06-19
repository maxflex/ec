<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\{
    GroupController,
    TestController,
};

Route::middleware(['auth:crm'])->group(function () {
    Route::controller(TestController::class)->prefix('tests')->group(function () {
        Route::get('/', 'index');
        Route::get('active', 'active');
        Route::get('results/{clientTest}', 'results');
        Route::get('/{test}', 'show');
        Route::post('start/{clientTest}', 'start');
        Route::post('finish', 'finish');
    });
    Route::apiResource('groups', GroupController::class)->only('index');
});

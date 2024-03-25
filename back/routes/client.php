<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\{
    TestController,
};

Route::middleware(['auth:crm'])->group(function () {
    Route::controller(TestController::class)->prefix('tests')->group(function () {
        Route::post('start/{test}', 'start');
        Route::get('active', 'active');
        Route::post('finish', 'finish');
    });
    Route::apiResources([
        'tests' => TestController::class,
    ]);
});

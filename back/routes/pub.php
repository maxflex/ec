<?php

use App\Http\Controllers\Pub\RequestsController;
use Illuminate\Support\Facades\Route;

Route::prefix('requests')->controller(RequestsController::class)->group(function () {
    Route::post('/', 'store');
    Route::post('/verify', 'verify');
});
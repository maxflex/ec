<?php

use App\Http\Controllers\Crm\RequestsController;
use Illuminate\Support\Facades\Route;

Route::apiResource('requests', RequestsController::class);

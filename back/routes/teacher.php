<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\{
    GroupController,
    LessonController,
};

Route::middleware(['auth:crm'])->group(function () {
    Route::apiResource('groups', GroupController::class);
    Route::apiResource('lessons', LessonController::class)->only('index');
});

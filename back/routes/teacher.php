<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\{
    GroupController,
    LessonController,
    ScheduleController,
};

Route::middleware(['auth:crm'])->group(function () {
    Route::get('schedule/teacher/{teacher}', [ScheduleController::class, 'teacher']);
    Route::apiResource('groups', GroupController::class);
    Route::apiResource('lessons', LessonController::class)->only('index');
});

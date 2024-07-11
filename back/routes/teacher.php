<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\{
    BalanceController,
    GradeController,
    GroupController,
    LessonController,
    ReportController,
    ScheduleController,
};

Route::middleware(['auth:crm'])->group(function () {
    Route::controller(ScheduleController::class)->prefix('schedule')->group(function () {
        Route::get('teacher/{teacher}', 'teacher');
        Route::get('group/{group}', 'group');
    });
    Route::get('balance/teacher/{teacher}', [BalanceController::class, 'teacher']);
    Route::apiResource('groups', GroupController::class);
    Route::post('lessons/conduct/{lesson}', [LessonController::class, 'conduct']);
    Route::apiResource('lessons', LessonController::class)->only([
        'index', 'update', 'show'
    ]);
    Route::apiResources([
        'reports' => ReportController::class,
        'grades' => GradeController::class,
    ]);
});

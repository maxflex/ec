<?php

use App\Http\Controllers\Teacher\{BalanceController,
    ClientGroupController,
    ClientReviewController,
    GradeController,
    GroupController,
    InstructionController,
    LessonController,
    ReportController};
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:crm'])->group(function () {
    Route::get('balance', BalanceController::class);
    Route::apiResource('groups', GroupController::class);
    Route::post('lessons/conduct/{lesson}', [LessonController::class, 'conduct']);
    Route::apiResource('lessons', LessonController::class)->only([
        'index', 'update', 'show'
    ]);
    Route::get('groups/visits/{group}', [GroupController::class, 'visits']);
    Route::apiResource('client-groups', ClientGroupController::class)->only('index');
    Route::get('instructions/diff/{instruction}', [InstructionController::class, 'diff']);
    Route::post('instructions/sign/{instruction}', [InstructionController::class, 'sign']);
    Route::apiResource('instructions', InstructionController::class)->only('index', 'show');
    Route::apiResources([
        'reports' => ReportController::class,
        'grades' => GradeController::class,
        'client-reviews' => ClientReviewController::class,
    ]);
});

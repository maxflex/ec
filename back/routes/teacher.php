<?php

use App\Http\Controllers\Teacher\{BalanceController,
    ClientGroupController,
    ClientReviewController,
    FileController,
    GradeController,
    GroupController,
    InstructionController,
    LessonController,
    ReportController,
    TeacherPaymentController};
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
    Route::apiResource('teacher-payments', TeacherPaymentController::class)->only('index');
    Route::prefix('instructions')->controller(InstructionController::class)->group(function () {
        Route::get('diff/{instruction}', 'diff');
        Route::post('sign/{instruction}', 'sign');
    });
    Route::post('files', FileController::class);
    Route::apiResource('instructions', InstructionController::class)->only('index', 'show');
    Route::get('reports/lessons', [ReportController::class, 'lessons']);
    Route::apiResources([
        'reports' => ReportController::class,
        'grades' => GradeController::class,
        'client-reviews' => ClientReviewController::class,
    ]);
});

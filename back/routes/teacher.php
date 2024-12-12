<?php

use App\Http\Controllers\Teacher\{BalanceController,
    BalanceVerificationController,
    ClientController,
    ClientGroupController,
    ClientReviewController,
    ClientTestController,
    CommentController,
    ExamScoreController,
    GradeController,
    GroupController,
    HeadTeacherReportController,
    InstructionController,
    LessonController,
    MenuCountsController,
    ReportController,
    ScholarshipScoreController,
    TeacherPaymentController};
use App\Http\Middleware\{HeadTeacherMiddleware};
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
    Route::apiResource('instructions', InstructionController::class)->only('index', 'show');
    Route::apiResource('clients', ClientController::class)->only('index', 'show');
    Route::get('reports/lessons', [ReportController::class, 'lessons']);
    Route::apiResources([
        'reports' => ReportController::class,
        'head-teacher-reports' => HeadTeacherReportController::class,
        'grades' => GradeController::class,
        'client-reviews' => ClientReviewController::class,
        'comments' => CommentController::class,
        'scholarship-scores' => ScholarshipScoreController::class,
    ]);

    Route::middleware(HeadTeacherMiddleware::class)->group(function () {
        Route::apiResource('exam-scores', ExamScoreController::class)->only('index');
        Route::apiResource('client-tests', ClientTestController::class)->only('index');
    });

    Route::get('menu-counts', MenuCountsController::class);

    Route::prefix('balance-verification')->controller(BalanceVerificationController::class)->group(function () {
        Route::get('check', 'check');
        Route::post('send-code', 'sendCode');
        Route::post('check-code', 'checkCode');
    });
});

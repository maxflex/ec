<?php

use App\Http\Controllers\ExamDateController;
use App\Http\Controllers\Teacher\BalanceController;
use App\Http\Controllers\Teacher\BalanceVerificationController;
use App\Http\Controllers\Teacher\ClientController;
use App\Http\Controllers\Teacher\ClientGroupController;
use App\Http\Controllers\Teacher\ClientReviewController;
use App\Http\Controllers\Teacher\ClientTestController;
use App\Http\Controllers\Teacher\CommentController;
use App\Http\Controllers\Teacher\ExamScoreController;
use App\Http\Controllers\Teacher\GradeController;
use App\Http\Controllers\Teacher\GroupController;
use App\Http\Controllers\Teacher\HeadTeacherReportController;
use App\Http\Controllers\Teacher\InstructionCheckController;
use App\Http\Controllers\Teacher\InstructionController;
use App\Http\Controllers\Teacher\LessonController;
use App\Http\Controllers\Teacher\LogController;
use App\Http\Controllers\Teacher\MenuCountsController;
use App\Http\Controllers\Teacher\ReportController;
use App\Http\Controllers\Teacher\ScholarshipScoreController;
use App\Http\Controllers\Teacher\TeacherPaymentController;
use App\Http\Controllers\TeethController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\VacationController;
use App\Http\Middleware\HeadTeacherMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:crm'])->group(function () {
    Route::get('balance', BalanceController::class);
    Route::apiResource('groups', GroupController::class);
    Route::post('lessons/conduct/{lesson}', [LessonController::class, 'conduct']);
    Route::apiResource('lessons', LessonController::class)->only([
        'index', 'update', 'show',
    ]);
    Route::get('groups/visits/{group}', [GroupController::class, 'visits']);
    Route::apiResource('client-groups', ClientGroupController::class)->only('index');
    Route::apiResource('teacher-payments', TeacherPaymentController::class)->only('index');
    Route::prefix('instructions')->controller(InstructionController::class)->group(function () {
        Route::get('diff/{instruction}', 'diff');
        Route::post('sign/{instruction}', 'sign');
    });
    Route::apiResource('instructions', InstructionController::class)->only('index', 'show');
    Route::apiResource('instructions-check', InstructionCheckController::class)->except('destroy');
    Route::apiResource('clients', ClientController::class)->only('index', 'show');
    Route::get('reports/tabs', [ReportController::class, 'tabs']);
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

    Route::apiResource('logs', LogController::class)->only('store');
    Route::apiResource('vacations', VacationController::class)->only('index');
    Route::get('teeth', TeethController::class);
    Route::apiResource('exam-dates', ExamDateController::class)->only('index');

    Route::prefix('upload')->controller(UploadController::class)->group(function () {
        Route::post('files', 'files');
        Route::post('photos', 'photos');
    });
});

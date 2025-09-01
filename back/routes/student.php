<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\Pub\SbpController;
use App\Http\Controllers\Student\BillingController;
use App\Http\Controllers\Student\GradeController;
use App\Http\Controllers\Student\GroupController;
use App\Http\Controllers\Student\JournalController;
use App\Http\Controllers\Student\LessonController;
use App\Http\Controllers\Student\ReportController;
use App\Http\Controllers\Student\StudentTestController;
use App\Http\Controllers\TeethController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\VacationController;
use Illuminate\Support\Facades\Route;

Route::controller(StudentTestController::class)->prefix('student-tests')->group(function () {
    Route::get('active', 'active');
    Route::post('start/{clientTest}', 'start');
    Route::post('finish', 'finish');
});

Route::apiResource('sbp', SbpController::class)->only('store');
Route::apiResource('lessons', LessonController::class)->only(['index', 'show']);
Route::get('billing', BillingController::class);
Route::apiResource('groups', GroupController::class)->only('index');
Route::get('grades/journal', [GradeController::class, 'journal']);
Route::apiResource('grades', GradeController::class)->only('index');
Route::apiResource('reports', ReportController::class)->only(['index', 'show']);
Route::apiResource('student-tests', StudentTestController::class)->only('index', 'show');
Route::get('journal', JournalController::class);
Route::apiResource('logs', LogController::class)->only('store');
Route::apiResource('vacations', VacationController::class)->only('index');
Route::get('teeth', TeethController::class);
Route::apiResource('events', EventController::class)->only('index');
Route::prefix('upload')->controller(UploadController::class)->group(function () {
    Route::post('photos', 'photos');
});

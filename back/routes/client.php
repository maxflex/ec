<?php

use App\Http\Controllers\Client\BillingController;
use App\Http\Controllers\Client\ClientTestController;
use App\Http\Controllers\Client\GradeController;
use App\Http\Controllers\Client\GroupController;
use App\Http\Controllers\Client\JournalController;
use App\Http\Controllers\Client\LessonController;
use App\Http\Controllers\Client\LogController;
use App\Http\Controllers\Client\ReportController;
use App\Http\Controllers\ExamDateController;
use App\Http\Controllers\TeethController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\VacationController;
use Illuminate\Support\Facades\Route;

Route::controller(ClientTestController::class)->prefix('client-tests')->group(function () {
    Route::get('active', 'active');
    Route::post('start/{clientTest}', 'start');
    Route::post('finish', 'finish');
});
Route::apiResource('lessons', LessonController::class)->only(['index', 'show']);
Route::get('billing', BillingController::class);
Route::apiResource('groups', GroupController::class)->only('index');
Route::get('grades/journal', [GradeController::class, 'journal']);
Route::apiResource('grades', GradeController::class)->only('index');
Route::apiResource('reports', ReportController::class)->only(['index', 'show']);
Route::apiResource('client-tests', ClientTestController::class)->only('index', 'show');
Route::get('journal', JournalController::class);
Route::apiResource('logs', LogController::class)->only('store');
Route::apiResource('vacations', VacationController::class)->only('index');
Route::get('teeth', TeethController::class);
Route::apiResource('exam-dates', ExamDateController::class)->only('index');
Route::apiResource('events', EventController::class)->only('index');
Route::prefix('upload')->controller(UploadController::class)->group(function () {
    Route::post('photos', 'photos');
});

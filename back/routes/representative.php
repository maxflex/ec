<?php

use App\Http\Controllers\Client\EventParticipantController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\Pub\SbpController;
use App\Http\Controllers\Representative\BillingController;
use App\Http\Controllers\Representative\ClientTestController;
use App\Http\Controllers\Representative\EventController;
use App\Http\Controllers\Representative\GradeController;
use App\Http\Controllers\Representative\GroupController;
use App\Http\Controllers\Representative\JournalController;
use App\Http\Controllers\Representative\LessonController;
use App\Http\Controllers\Representative\ReportController;
use App\Http\Controllers\TeethController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\VacationController;
use Illuminate\Support\Facades\Route;

Route::controller(ClientTestController::class)->prefix('client-tests')->group(function () {
    Route::get('active', 'active');
    Route::post('start/{clientTest}', 'start');
    Route::post('finish', 'finish');
});

Route::apiResource('sbp', SbpController::class)->only('store');
Route::apiResource('lessons', LessonController::class)->only('index');
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
Route::apiResource('events', EventController::class)->only(['index', 'show']);
Route::apiResource('event-participants', EventParticipantController::class)->only(['index', 'update']);
Route::prefix('upload')->controller(UploadController::class)->group(function () {
    Route::post('photos', 'photos');
});

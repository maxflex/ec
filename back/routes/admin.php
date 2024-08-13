<?php

use App\Http\Controllers\Admin\BalanceController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientPaymentController;
use App\Http\Controllers\Admin\ClientReviewController;
use App\Http\Controllers\Admin\ClientTestController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\ContractPaymentController;
use App\Http\Controllers\Admin\ContractVersionController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventParticipantController;
use App\Http\Controllers\Admin\ExamScoreController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\GroupContractController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\InstructionController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\MacroController;
use App\Http\Controllers\Admin\PersonController;
use App\Http\Controllers\Admin\PhotoController;
use App\Http\Controllers\Admin\PreviewController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RequestsController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\SwampController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TeacherPaymentController;
use App\Http\Controllers\Admin\TeacherServiceController;
use App\Http\Controllers\Admin\TeethController;
use App\Http\Controllers\Admin\TelegramMessageController;
use App\Http\Controllers\Admin\TestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VacationController;
use App\Http\Controllers\Admin\WebReviewController;
use App\Http\Controllers\Common\LogController;
use App\Http\Controllers\TopicController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:crm'])->group(function () {
    Route::post('tests/upload-pdf/{test}', [TestController::class, 'uploadPdf']);
    Route::post('preview', [PreviewController::class, 'enter']);
    Route::post('photos/upload', [PhotoController::class, 'upload']);
    Route::apiResource('photos', PhotoController::class)->only('store');
    Route::post('stats', StatsController::class);
    Route::controller(BalanceController::class)->prefix('balance')->group(function () {
        Route::get('contract/{contract}', 'contract');
        Route::get('teacher/{teacher}', 'teacher');
    });
    Route::controller(ScheduleController::class)->prefix('schedule')->group(function () {
        Route::get('client/{client}', 'client');
        Route::get('teacher/{teacher}', 'teacher');
        Route::get('group/{group}', 'group');
    });
    Route::apiResource('logs', LogController::class)->only('index');
    Route::apiResource('topics', TopicController::class)->only('index');
    Route::get('persons', PersonController::class);
    Route::post('lessons/batch', [LessonController::class, 'batch']);

    // Групповое редактирование уроков
    Route::prefix('lessons/batch')->controller(LessonController::class)->group(function () {
        Route::post('/', 'batchStore');
        Route::put('/', 'batchUpdate');
        Route::delete('/', 'batchDestroy');
    });

    Route::post('telegram-messages/bulk', [TelegramMessageController::class, 'bulkStore']);

    Route::post('groups/bulk-store-candidates/{group}', [GroupController::class, 'bulkStoreCandidates']);
    Route::get('groups/candidates/{group}', [GroupController::class, 'candidates']);
    Route::get('groups/visits/{group}', [GroupController::class, 'visits']);

    Route::get('instructions/diff/{instruction}', [InstructionController::class, 'diff']);

    Route::get('teeth', TeethController::class);

    Route::apiResource('swamps', SwampController::class)->only('index');

    Route::prefix('event-participants')->controller(EventParticipantController::class)->group(function () {
        Route::post('/', 'store');
        Route::put('/{eventParticipant}', 'update');
        Route::get('/{event}', 'show');
    });

    Route::apiResources([
        'requests' => RequestsController::class,
        'clients' => ClientController::class,
        'groups' => GroupController::class,
        'group-contracts' => GroupContractController::class,
        'contracts' => ContractController::class,
        'contract-versions' => ContractVersionController::class,
        'contract-payments' => ContractPaymentController::class,
        'vacations' => VacationController::class,
        'macros' => MacroController::class,
        'tests' => TestController::class,
        'client-tests' => ClientTestController::class,
        'teacher-services' => TeacherServiceController::class,
        'teacher-payments' => TeacherPaymentController::class,
        'teachers' => TeacherController::class,
        'users' => UserController::class,
        'comments' => CommentController::class,
        'lessons' => LessonController::class,
        'client-reviews' => ClientReviewController::class,
        'client-payments' => ClientPaymentController::class,
        'web-reviews' => WebReviewController::class,
        'reports' => ReportController::class,
        'events' => EventController::class,
        'grades' => GradeController::class,
        'exam-scores' => ExamScoreController::class,
        'telegram-messages' => TelegramMessageController::class,
        'instructions' => InstructionController::class,
    ]);
});

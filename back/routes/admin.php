<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    BalanceController,
    RequestsController,
    ClientController,
    ClientPaymentController,
    ClientReviewController,
    ClientTestController,
    ContractPaymentController,
    CommentController,
    ContractController,
    ContractVersionController,
    EventController,
    EventParticipantController,
    ExamScoreController,
    GradeController,
    GroupController,
    InstructionController,
    LessonController,
    MacroController,
    PersonController,
    PreviewController,
    PhotoController,
    ReportController,
    ScheduleController,
    StatsController,
    SwampController,
    TeacherController,
    TeacherPaymentController,
    TeacherServiceController,
    TelegramMessageController,
    TestController,
    UserController,
    VacationController,
    WebReviewController,
};
use App\Http\Controllers\Common\LogController;
use App\Http\Controllers\TopicController;

Route::middleware(['auth:crm'])->group(function () {
    // TODO: улучшить
    Route::controller(GroupController::class)->prefix('groups')->group(function () {
        Route::post('add-client', 'addClient');
        Route::post('remove-contract', 'removeContract');
    });
    Route::post('tests/upload-pdf/{test}', [TestController::class, 'uploadPdf']);
    Route::post('preview', [PreviewController::class, 'enter']);
    Route::apiResource('photos', PhotoController::class)->only('store');
    Route::apiResource('stats', StatsController::class)->only('index');
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

    Route::apiResource('swamps', SwampController::class)->only('index');

    Route::apiResource('event-participants', EventParticipantController::class)->only(['update']);

    Route::apiResources([
        'requests' => RequestsController::class,
        'clients' => ClientController::class,
        'groups' => GroupController::class,
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

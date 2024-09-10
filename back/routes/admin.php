<?php

use App\Http\Controllers\Admin\{BalanceController,
    CallController,
    ClientController,
    ClientGroupController,
    ClientPaymentController,
    ClientReviewController,
    ClientTestController,
    CommentController,
    ContractController,
    ContractPaymentController,
    ContractVersionController,
    ErrorController,
    EventParticipantController,
    ExamScoreController,
    GradeController,
    GroupController,
    InstructionController,
    LessonController,
    MacroController,
    MangoTestController,
    PersonController,
    PhotoController,
    PreviewController,
    ReportController,
    RequestsController,
    StatsController,
    SwampController,
    TeacherBalanceController,
    TeacherController,
    TeacherPaymentController,
    TeacherServiceController,
    TeethController,
    TelegramMessageController,
    TestController,
    TopicController,
    UserController,
    WebReviewController};
use App\Http\Controllers\Common\LogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:crm'])->group(function () {
    Route::post('tests/upload-pdf/{test}', [TestController::class, 'uploadPdf']);
    Route::post('preview', [PreviewController::class, 'enter']);
    Route::post('photos/upload', [PhotoController::class, 'upload']);
    Route::apiResource('photos', PhotoController::class)->only('store');
    Route::post('stats', StatsController::class);

    Route::get('balance', BalanceController::class);

    Route::apiResource('logs', LogController::class)->only('index');
    Route::apiResource('topics', TopicController::class)->only('index');
    Route::get('persons', PersonController::class);
    Route::post('lessons/bulk', [LessonController::class, 'bulk']);

    Route::post('mango-test/{event}', MangoTestController::class);

    Route::prefix('lessons')->controller(LessonController::class)->group(function () {
        Route::post('upload-file', 'uploadFile');
        // Групповое редактирование уроков
        Route::prefix('bulk')->group(function () {
            Route::post('/', 'bulkStore');
            Route::put('/', 'bulkUpdate');
            Route::delete('/', 'bulkDestroy');
        });
    });

    Route::prefix('groups')->controller(GroupController::class)->group(function () {
        Route::post('bulk-store-candidates/{group}', 'bulkStoreCandidates');
        Route::get('candidates/{group}', 'candidates');
        Route::get('visits/{group}', 'visits');
    });

    Route::get('instructions/diff/{instruction}', [InstructionController::class, 'diff']);

    Route::get('teeth', TeethController::class);

    Route::apiResource('swamps', SwampController::class)->only('index');

    Route::prefix('event-participants')->controller(EventParticipantController::class)->group(function () {
        Route::post('/', 'store');
        Route::put('/{eventParticipant}', 'update');
        Route::get('/{event}', 'show');
    });

    Route::get('teacher-balances', TeacherBalanceController::class);

    Route::prefix('calls')->controller(CallController::class)->group(function () {
        Route::get('active', 'active');
        Route::get('recording/{action}/{call}', 'recording');
    });

    Route::prefix('errors')->controller(ErrorController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/check', 'check');
    });

    Route::apiResources([
        'requests' => RequestsController::class,
        'clients' => ClientController::class,
        'groups' => GroupController::class,
        'client-groups' => ClientGroupController::class,
        'contracts' => ContractController::class,
        'contract-versions' => ContractVersionController::class,
        'contract-payments' => ContractPaymentController::class,
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
        'grades' => GradeController::class,
        'exam-scores' => ExamScoreController::class,
        'telegram-messages' => TelegramMessageController::class,
        'instructions' => InstructionController::class,
        'calls' => CallController::class,
    ]);
});

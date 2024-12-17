<?php

use App\Http\Controllers\Admin\{AllPaymentsController,
    BalanceController,
    CallController,
    ClientController,
    ClientGroupController,
    ClientPaymentController,
    ClientReviewController,
    ClientTestController,
    CommentController,
    ContractBalanceController,
    ContractController,
    ContractPaymentController,
    ContractVersionController,
    ErrorController,
    EventParticipantController,
    ExamScoreController,
    FreeCabinetController,
    GradeController,
    GroupActController,
    GroupController,
    HeadTeacherReportController,
    InstructionController,
    LessonController,
    MacroController,
    MenuCountsController,
    PassController,
    PeopleSelectorController,
    PhotoController,
    PreviewController,
    PrintController,
    ReportController,
    RequestsController,
    ScholarshipScoreController,
    SearchController,
    StatsController,
    SwampController,
    TeacherBalanceController,
    TeacherController,
    TeacherPaymentController,
    TeacherServiceController,
    TelegramListController,
    TelegramMessageController,
    TestController,
    TopicController,
    UserController,
    WebReviewController};
use App\Http\Controllers\Common\LogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:crm'])->group(function () {
    Route::post('tests/upload-pdf/{test}', [TestController::class, 'uploadPdf']);
    Route::post('preview', PreviewController::class);
    Route::post('photos/upload', [PhotoController::class, 'upload']);
    Route::apiResource('photos', PhotoController::class)->only('store');
    Route::post('stats', StatsController::class);

    Route::get('balance', BalanceController::class);

    Route::apiResource('logs', LogController::class)->only('index');
    Route::apiResource('topics', TopicController::class)->only('index');
    Route::post('lessons/bulk', [LessonController::class, 'bulk']);

    Route::prefix('lessons')->controller(LessonController::class)->group(function () {
        // Групповое редактирование уроков
        Route::prefix('bulk')->group(function () {
            Route::post('/', 'bulkStore');
            Route::put('/', 'bulkUpdate');
            Route::delete('/', 'bulkDestroy');
        });
    });

    Route::get('parents/{clientParent}', [ClientController::class, 'clientParent']);

    Route::prefix('groups')->controller(GroupController::class)->group(function () {
        Route::post('bulk-store-candidates/{group}', 'bulkStoreCandidates');
        Route::get('candidates/{group}', 'candidates');
        Route::get('visits/{group}', 'visits');
    });

    Route::get('instructions/diff/{instruction}', [InstructionController::class, 'diff']);

    Route::apiResource('swamps', SwampController::class)->only('index');

    Route::prefix('event-participants')->controller(EventParticipantController::class)->group(function () {
        Route::post('/', 'store');
        Route::put('/{eventParticipant}', 'update');
        Route::get('/{event}', 'show');
    });

    Route::get('teacher-balances', TeacherBalanceController::class);
    Route::get('contract-balances', ContractBalanceController::class);

    Route::prefix('calls')->controller(CallController::class)->group(function () {
        Route::get('active', 'active');
        Route::get('recording/{action}/{call}', 'recording');
    });

    Route::prefix('errors')->controller(ErrorController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/check', 'check');
    });

    Route::get('search', SearchController::class);
    Route::apiResource('print', PrintController::class)->only('show');

    Route::get('people-selector', PeopleSelectorController::class);

    Route::get('free-cabinets', FreeCabinetController::class);

    Route::get('scholarship-scores', ScholarshipScoreController::class);

    Route::post('telegram-lists/load-people', [TelegramListController::class, 'loadPeople']);

    Route::get('requests/associated/{request}', [RequestsController::class, 'associated']);

    Route::get('passes/permanent', [PassController::class, 'permanent']);

    Route::get('menu-counts', MenuCountsController::class);

    Route::get('all-payments', AllPaymentsController::class);
   
    Route::apiResources([
        'telegram-lists' => TelegramListController::class,
        'requests' => RequestsController::class,
        'clients' => ClientController::class,
        'groups' => GroupController::class,
        'group-acts' => GroupActController::class,
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
        'passes' => PassController::class,
        'head-teacher-reports' => HeadTeacherReportController::class,
    ]);
});

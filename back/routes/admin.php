<?php

use App\Http\Controllers\Admin\AllLessonsController;
use App\Http\Controllers\Admin\AllPaymentsController;
use App\Http\Controllers\Admin\CabinetController;
use App\Http\Controllers\Admin\CallController;
use App\Http\Controllers\Admin\ClientComplaintController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientGroupController;
use App\Http\Controllers\Admin\ClientLessonController;
use App\Http\Controllers\Admin\ClientPaymentController;
use App\Http\Controllers\Admin\ClientReviewController;
use App\Http\Controllers\Admin\ClientTestController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\ContractBalanceController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\ContractPaymentController;
use App\Http\Controllers\Admin\ContractVersionController;
use App\Http\Controllers\Admin\ContractVersionProgramController;
use App\Http\Controllers\Admin\ControlController;
use App\Http\Controllers\Admin\ErrorController;
use App\Http\Controllers\Admin\EventParticipantController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamScoreController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\GroupActController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\HeadTeacherReportController;
use App\Http\Controllers\Admin\InstructionController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\MacroController;
use App\Http\Controllers\Admin\MenuCountsController;
use App\Http\Controllers\Admin\PassController;
use App\Http\Controllers\Admin\PeopleSelectorController;
use App\Http\Controllers\Admin\PreviewController;
use App\Http\Controllers\Admin\PrintController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RequestsController;
use App\Http\Controllers\Admin\ScheduleDraftController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SmsMessageController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\StatsPresetController;
use App\Http\Controllers\Admin\SwampController;
use App\Http\Controllers\Admin\TeacherBalanceController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TeacherPaymentController;
use App\Http\Controllers\Admin\TeacherServiceController;
use App\Http\Controllers\Admin\TelegramListController;
use App\Http\Controllers\Admin\TelegramMessageController;
use App\Http\Controllers\Admin\TestController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WebReviewController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\TeethController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\VacationController;
use Illuminate\Support\Facades\Route;

Route::post('preview', PreviewController::class);
Route::post('stats', StatsController::class);

Route::get('balance', BalanceController::class);

Route::prefix('upload')->controller(UploadController::class)->group(function () {
    Route::post('files', 'files');
    Route::post('photos', 'photos');
    Route::post('instructions', 'instructions');
});

Route::get('teacher-payments/get-suggestions/{teacher}', [TeacherPaymentController::class, 'getSuggestions']);

Route::prefix('control')->controller(ControlController::class)->group(function () {
    Route::get('lk', 'lk');
    Route::get('lessons', 'lessons');
    Route::get('grades', 'grades');
});

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

Route::prefix('schedule-drafts')->controller(ScheduleDraftController::class)->group(function () {
    Route::post('/add-to-group', 'addToGroup');
    Route::post('/remove-from-group', 'removeFromGroup');
    Route::post('/add-programs', 'addPrograms');
    Route::post('/remove-program', 'removeProgram');
    Route::post('/save', 'save');
    Route::post('/apply-move-groups', 'applyMoveGroups');
    Route::get('/load/{scheduleDraft}', 'load');
    Route::get('/from-actual-contracts', 'fromActualContracts');
    Route::get('/get-teeth', 'getTeeth');
});
Route::apiResource('schedule-drafts', ScheduleDraftController::class)->only(['index', 'show', 'destroy']);

Route::prefix('event-participants')->controller(EventParticipantController::class)->group(function () {
    Route::post('/', 'store');
    Route::put('/{eventParticipant}', 'update');
    Route::delete('/{eventParticipant}', 'destroy');
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
Route::post('print', PrintController::class);

Route::get('people-selector', PeopleSelectorController::class);

Route::get('cabinets/free', [CabinetController::class, 'free']);
Route::get('cabinets', [CabinetController::class, 'index']);

Route::post('telegram-lists/load-people', [TelegramListController::class, 'loadPeople']);

Route::get('requests/associated/{request}', [RequestsController::class, 'associated']);

Route::get('passes/permanent', [PassController::class, 'permanent']);
Route::get('passes/stats', [PassController::class, 'stats']);

Route::get('menu-counts', MenuCountsController::class);

Route::get('all-payments', AllPaymentsController::class);

Route::get('contract-version-programs', ContractVersionProgramController::class);

Route::get('all-lessons', AllLessonsController::class);

Route::get('reports/tabs', [ReportController::class, 'tabs']);

Route::get('teachers/stats/{teacher}', [TeacherController::class, 'stats']);

Route::apiResource('vacations', VacationController::class)->only(['index', 'store']);
Route::apiResource('logs', LogController::class)->only(['index', 'store']);

Route::get('teeth', TeethController::class);
Route::get('exams', ExamController::class);
Route::apiResource('sms-messages', SmsMessageController::class)->only('index');

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
    'client-complaints' => ClientComplaintController::class,
    'client-reviews' => ClientReviewController::class,
    'teacher-services' => TeacherServiceController::class,
    'teacher-payments' => TeacherPaymentController::class,
    'teachers' => TeacherController::class,
    'users' => UserController::class,
    'comments' => CommentController::class,
    'lessons' => LessonController::class,
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
    'stats-presets' => StatsPresetController::class,
    'events' => EventController::class,
    'client-lessons' => ClientLessonController::class,
]);

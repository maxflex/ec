<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    BalanceController,
    RequestsController,
    ClientController,
    ClientPaymentController,
    ClientReviewController,
    CommentController,
    ContractController,
    ContractVersionController,
    GroupController,
    LessonController,
    MacroController,
    PreviewController,
    PhotoController,
    ScheduleController,
    StatsController,
    TeacherController,
    TeacherPaymentController,
    TestController,
    UserController,
    VacationController,
    WebReviewController,
};

Route::middleware(['auth:crm'])->group(function () {
    // TODO: улучшить
    Route::controller(GroupController::class)->prefix('groups')->group(function () {
        Route::post('add-client', 'addClient');
        Route::post('remove-contract', 'removeContract');
    });
    Route::controller(TestController::class)->prefix('tests')->group(function () {
        Route::post('add-client/{client}', 'addClient');
        Route::post('upload-pdf/{test}', 'uploadPdf');
    });
    Route::post('preview', [PreviewController::class, 'enter']);
    Route::get('tests/results/{clientTest}', [TestController::class, 'results']);
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
    Route::apiResources([
        'requests' => RequestsController::class,
        'clients' => ClientController::class,
        'groups' => GroupController::class,
        'contracts' => ContractController::class,
        'contract-versions' => ContractVersionController::class,
        'vacations' => VacationController::class,
        'macros' => MacroController::class,
        'tests' => TestController::class,
        'teacher-payments' => TeacherPaymentController::class,
        'teachers' => TeacherController::class,
        'users' => UserController::class,
        'comments' => CommentController::class,
        'lessons' => LessonController::class,
        'client-reviews' => ClientReviewController::class,
        'client-payments' => ClientPaymentController::class,
        'web-reviews' => WebReviewController::class,
    ]);
});

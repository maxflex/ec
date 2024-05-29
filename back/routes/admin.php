<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    RequestsController,
    ClientController,
    CommentController,
    ContractController,
    GroupController,
    MacroController,
    PreviewController,
    TeacherController,
    TestController,
    UserController,
    VacationController
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
    Route::apiResources([
        'requests' =>  RequestsController::class,
        'clients' =>  ClientController::class,
        'groups' =>  GroupController::class,
        'contracts' =>  ContractController::class,
        'vacations' => VacationController::class,
        'macros' => MacroController::class,
        'tests' => TestController::class,
        'teachers' => TeacherController::class,
        'users' => UserController::class,
        'comments' => CommentController::class,
    ]);
});

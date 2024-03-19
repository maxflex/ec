<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Crm\{
    AuthController,
    RequestsController,
    ClientController,
    ContractController,
    GroupController,
    MacroController,
    TelegramController,
    TestController,
    VacationController
};

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('login', 'login');
    Route::post('preview', 'preview');
    Route::post('verify-code', 'verifyCode');
});

Route::controller(TelegramController::class)->prefix('telegram')->group(function () {
});

Route::post('telegram', [TelegramController::class, 'bot']);

Route::middleware(['auth:crm'])->group(function () {
    Route::get('auth/user', [AuthController::class, 'user']);
    // TODO: улучшить
    Route::controller(GroupController::class)->prefix('groups')->group(function () {
        Route::post('add-client', 'addClient');
        Route::post('remove-contract', 'removeContract');
    });
    Route::controller(TestController::class)->prefix('tests')->group(function () {
        Route::post('add-client/{client}', 'addClient');
        Route::post('upload-pdf/{test}', 'uploadPdf');
    });
    Route::apiResources([
        'requests' =>  RequestsController::class,
        'clients' =>  ClientController::class,
        'groups' =>  GroupController::class,
        'contracts' =>  ContractController::class,
        'vacations' => VacationController::class,
        'macros' => MacroController::class,
        'tests' => TestController::class,
    ]);
});

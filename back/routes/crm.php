<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Crm\{
    AuthController,
    RequestsController,
    ClientController,
    ContractController,
    GroupController,
    MacroController,
    TestController,
    VacationController
};

// TODO: улучшить
Route::controller(GroupController::class)->prefix('groups')->group(function () {
    Route::post('add-client', 'addClient');
    Route::post('remove-contract', 'removeContract');
});

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::get('user', 'user');
    Route::post('login', 'login');
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

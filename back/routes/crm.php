<?php

use App\Http\Controllers\Crm\{RequestsController, ClientController, ContractController, GroupController, MacroController, TestController, VacationController};
use Illuminate\Support\Facades\Route;

Route::apiResource('requests', RequestsController::class);

// TODO: улучшить
Route::post('groups/add-client', [GroupController::class, 'addClient']);
Route::post('groups/remove-contract', [GroupController::class, 'removeContract']);

Route::post('tests/upload-pdf/{test}', [TestController::class, 'uploadPdf']);

Route::apiResources([
    'requests' =>  RequestsController::class,
    'clients' =>  ClientController::class,
    'groups' =>  GroupController::class,
    'contracts' =>  ContractController::class,
    'vacations' => VacationController::class,
    'macros' => MacroController::class,
    'tests' => TestController::class,
]);

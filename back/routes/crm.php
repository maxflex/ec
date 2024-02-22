<?php

use App\Http\Controllers\Crm\{RequestsController, ClientController, ContractController, GroupController};
use Illuminate\Support\Facades\Route;

Route::apiResource('requests', RequestsController::class);

// TODO: улучшить
Route::post('groups/add-client', [GroupController::class, 'addClient']);
Route::post('groups/remove-contract', [GroupController::class, 'removeContract']);

Route::apiResources([
    'requests' =>  RequestsController::class,
    'clients' =>  ClientController::class,
    'groups' =>  GroupController::class,
    'contracts' =>  ContractController::class,
]);

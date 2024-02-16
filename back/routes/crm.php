<?php

use App\Http\Controllers\Crm\{RequestsController, ClientController, ContractController, GroupController};
use Illuminate\Support\Facades\Route;

Route::apiResource('requests', RequestsController::class);

Route::apiResources([
    'requests' =>  RequestsController::class,
    'clients' =>  ClientController::class,
    'groups' =>  GroupController::class,
    'contracts' =>  ContractController::class,
]);

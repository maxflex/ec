<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractPaymentResource;
use App\Models\ContractPayment;
use Illuminate\Http\Request;

class ContractPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = ContractPayment::query()->latest();
        return $this->handleIndexRequest($request, $query, ContractPaymentResource::class);
    }

    public function store(Request $request)
    {
        $clientService = auth()->user()->entity->clientServices()->create($request->all());
        return new ContractPaymentResource($clientService);
    }

    public function update(ContractPayment $clientService, Request $request)
    {
        $clientService->update($request->all());
        return new ContractPaymentResource($clientService);
    }

    public function destroy(ContractPayment $clientService)
    {
        $clientService->delete();
    }
}

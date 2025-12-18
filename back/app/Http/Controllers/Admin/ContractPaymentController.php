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
        $query = ContractPayment::query()
            ->with(['user', 'contract'])
            ->latest();

        return $this->handleIndexRequest(
            $request,
            $query,
            ContractPaymentResource::class
        );
    }

    public function show(ContractPayment $contractPayment)
    {
        return new ContractPaymentResource($contractPayment);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sum' => ['required', 'numeric', 'min:1'],
            'receipt_number' => ['required_unless:company,ooo'], // чек обязателен для всех, кроме ООО
        ]);

        $contractPayment = ContractPayment::create($request->all());

        return new ContractPaymentResource($contractPayment);
    }

    public function update(ContractPayment $contractPayment, Request $request)
    {
        $contractPayment->update($request->all());

        return new ContractPaymentResource($contractPayment);
    }

    // Платежи нельзя удалять, т.к. отправляется чек
    // public function destroy(ContractPayment $contractPayment)
    // {
    //     $contractPayment->delete();
    // }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OtherPaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\AllPaymentsResource;
use App\Http\Resources\OtherPaymentResource;
use App\Models\OtherPayment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OtherPaymentController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'sum' => ['required', 'numeric', 'min:1'],
            'date' => ['required', 'date_format:Y-m-d'],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'middle_name' => ['required', 'string'],
            'purpose' => ['required', 'string'],
            'method' => ['required', Rule::enum(OtherPaymentMethod::class)],
            'receipt_number' => ['required', 'phone'],
        ]);

        $otherPayment = OtherPayment::create($request->all());

        return new AllPaymentsResource(
            $otherPayment->formatForAllPayments()
        );
    }

    public function show(OtherPayment $otherPayment)
    {
        return new OtherPaymentResource($otherPayment);
    }

    public function update(OtherPayment $otherPayment, Request $request)
    {
        $otherPayment->update($request->all());

        return new AllPaymentsResource(
            $otherPayment->formatForAllPayments()
        );
    }

    // Платежи нельзя удалять, т.к. отправляется чек
    // public function destroy(OtherPayment $otherPayment)
    // {
    //     $otherPayment->delete();
    // }
}

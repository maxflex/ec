<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllPaymentsResource;
use App\Http\Resources\OtherPaymentResource;
use App\Models\OtherPayment;
use Illuminate\Http\Request;

class OtherPaymentController extends Controller
{
    public function store(Request $request)
    {
        $otherPayment = auth()->user()->otherPayments()->create($request->all());

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

    public function destroy(OtherPayment $otherPayment)
    {
        $otherPayment->delete();
    }
}

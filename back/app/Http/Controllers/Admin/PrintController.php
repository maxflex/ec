<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientPayment;
use App\Models\ContractPayment;
use App\Models\ContractVersion;
use App\Models\Group;
use App\Models\GroupAct;
use App\Models\Macro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;


class PrintController extends Controller
{
    public function show($id, Request $request)
    {
        $macro = Macro::findOrFail($id);
        $textField = null;

        if ($request->has('contract_version_id')) {
            $contractVersion = ContractVersion::find($request->contract_version_id);
            $textField = 'text_' . $contractVersion->contract->company->value;
        } elseif ($request->has('client_payment_id')) {
            $payment = ClientPayment::find($request->client_payment_id);
            $textField = 'text_' . $payment->company->value;
        } elseif ($request->has('contract_payment_id')) {
            $payment = ContractPayment::find($request->contract_payment_id);
            $textField = 'text_' . $payment->contract->company->value;
        } elseif ($request->has('act_id')) {
            $act = GroupAct::find($request->act_id);
            $textField = $request->text_field;
        } elseif ($request->has('group_id')) {
            $group = Group::find($request->group_id);
            $textField = $request->text_field;
        }

        // Render the template with Blade and pass variables
        $renderedText = Blade::render($macro->$textField, [
            'contractVersion' => $contractVersion ?? null,
            'payment' => $payment ?? null,
            'act' => $act ?? null,
            'group' => $group ?? null,
        ]);

        return ['text' => $renderedText];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Company;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientPayment;
use App\Models\ContractPayment;
use App\Models\ContractVersion;
use App\Models\Group;
use App\Models\GroupAct;
use App\Models\Macro;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\Rule;
use tbQuar\Facades\Quar;

class PrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:macros,id'],
            'company' => ['sometimes', 'required', Rule::enum(Company::class)],
        ]);

        $macro = Macro::findOrFail($request->input('id'));
        $company = Company::tryFrom($request->input('company'));
        $variables = [];

        if ($request->input('id') === 15) {
            $variables = [
                ...$request->all(),
                'client' => Client::find($request->client_id),
            ];
        } elseif ($request->has('contract_version_id')) {
            $contractVersion = ContractVersion::find($request->contract_version_id);
            $variables = compact('contractVersion');
            $company = $contractVersion->contract->company;
        } elseif ($request->has('client_payment_id')) {
            $payment = ClientPayment::find($request->client_payment_id);
            $variables = compact('payment');
            $company = $payment->company;
        } elseif ($request->has('contract_payment_id')) {
            $payment = ContractPayment::find($request->contract_payment_id);
            $qr = $this->generateQr($payment);
            $variables = compact('payment', 'qr');
            $company = $payment->contract->company;
        } elseif ($request->has('act_id')) {
            $act = GroupAct::find($request->act_id);
            $variables = compact('act');
        } elseif ($request->has('group_id')) {
            $group = Group::find($request->group_id);
            $variables = compact('group');
        }

        $field = 'text_'.$company->value;

        // Render the template with Blade and pass variables
        $renderedText = Blade::render($macro->$field, $variables);

        return ['text' => $renderedText];
    }

    private function generateQr(ContractPayment $payment)
    {
        $contract = $payment->contract;
        $data = $contract->company === Company::ooo ? [
            'Name' => 'ООО "ЕГЭ-Центр"',
            'PersonalAcc' => '40702810801960000153',
            'PayeeINN' => '9701038111',
            'KPP' => '770101001',
        ] : [
            'Name' => 'ИП Горшкова Анастасия Александровна',
            'PersonalAcc' => '40802810401400004731',
            'PayeeINN' => '622709802712',
            'KPP' => '',
        ];

        $data = collect([
            ...$data,
            'BankName' => 'АО "АЛЬФА-БАНК"',
            'BIC' => '044525593',
            'CorrespAcc' => '30101810200000000593',
            'Purpose' => sprintf(
                'Платные образовательные услуги по договору №%d от %s г.',
                $contract->id,
                Carbon::parse($contract->date)->format('d.m.Y')
            ),
            'LastName' => $contract->client->parent->last_name,
            'FirstName' => $contract->client->parent->first_name,
            'MiddleName' => $contract->client->parent->middle_name,
        ]);

        $qrValue = collect([
            'ST00012',
            ...$data->map(fn ($value, $key) => "$key=$value")->values()->all(),
        ])->join('|');

        return Quar::size(150)->generate($qrValue);
    }
}

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
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\Rule;

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
            $variables = compact('payment');
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

    private function getSpravkaVariables(Request $request)
    {
        $request->validate([
            'year' => ['required', 'numeric', 'min:2015'],
            'client_id' => ['required', 'numeric', 'exists:clients,id'],
            'seq' => ['required', 'numeric', 'min:1'],
            'parent_inn' => ['required', 'numeric'],
            'date' => ['required', 'date_format:Y-m-d'],
            'parent_birthday' => ['required', 'date_format:Y-m-d'],
            'client_passport_issued_at' => ['required', 'date_format:Y-m-d'],
            'client_inn' => ['required', 'numeric'],
            'sum' => ['required', 'numeric', 'min:1'],
            'company' => Rule::enum(Company::class),
        ]);

        $companyData = $request->input('company') === Company::ip->value ? [
            'company_name_line_1' => 'Индивидуальный предприниматель Горшкова',
            'company_name_line_2' => 'Анастасия Александровна',
            'inn' => '622709802712',
            'kpp' => '',
            'last_name' => 'Горшкова',
            'first_name' => 'Анастасия',
            'middle_name' => 'Александровна',
        ] : [
            'company_name_line_1' => 'Общество с ограниченной ответственностью',
            'company_name_line_2' => '«ЕГЭ-Центр»',
            'inn' => '9701038111',
            'kpp' => '770301001',
            'last_name' => 'Колбягина',
            'first_name' => 'Анастасия',
            'middle_name' => 'Тимофеевна',
        ];

        return ['text' => view('print.spravka-ip'), [
            ...$companyData,
            ...$request->all(),
            'year' => (string) $request->input('year'),
            'client' => Client::find($request->client_id),
        ]];
    }
}

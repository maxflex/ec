<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Company;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractPayment;
use App\Models\ContractVersion;
use App\Models\Group;
use App\Models\GroupAct;
use App\Models\Macro;
use App\Models\OtherPayment;
use App\Models\Teacher;
use App\Models\TeacherContract;
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

        $id = (int) $request->input('id');
        $macro = Macro::findOrFail($id);
        $company = Company::tryFrom($request->input('company'));
        $variables = [];

        // Справка об оплате образовательных услуг
        if ($id === 15) {
            $variables = [
                ...$request->all(),
                'client' => Client::find($request->client_id),
            ];
        } elseif ($id === 11) {
            // Счёт на оплату
            $contract = Contract::find($request->contract_id);
            $company = $contract->company;
            $variables = [
                ...$request->all(),
                'qr' => $this->generateQr($contract),
                'contract' => $contract,
            ];
        } elseif (in_array($id, [21, 22])) {
            // договор на преподавателя – все группы
            $teacher = Teacher::find($request->teacher_id);
            $company = Company::ano;
            $firstVersionDate = TeacherContract::query()
                ->where('teacher_id', $teacher->id)
                ->where('year', $request->input('year'))
                ->min('date') ?? $request->input('date');

            $variables = [
                ...$request->all(),
                'firstVersionDate' => $firstVersionDate,
                'data' => collect($request->input('data')),
                'teacher' => $teacher,
            ];
        } elseif ($request->has('contract_version_id')) {
            $contractVersion = ContractVersion::find($request->contract_version_id);
            $variables = compact('contractVersion');
            $company = $contractVersion->contract->company;
        } elseif ($request->has('other_payment_id')) {
            $payment = OtherPayment::find($request->other_payment_id);
            $variables = compact('payment');
            $company = Company::ooo;
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

    private function generateQr(Contract $contract)
    {
        $data = collect([
            ...$contract->company->bankAccount(),
            'Purpose' => sprintf(
                'Платные образовательные услуги по договору №%d от %s г.',
                $contract->id,
                Carbon::parse($contract->date)->format('d.m.Y')
            ),
            'LastName' => $contract->client->representative->last_name,
            'FirstName' => $contract->client->representative->first_name,
            'MiddleName' => $contract->client->representative->middle_name,
        ]);

        $qrValue = collect([
            'ST00012',
            ...$data->map(fn ($value, $key) => "$key=$value")->values()->all(),
        ])->join('|');

        return Quar::size(150)->generate($qrValue);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Teacher;
use App\Utils\Balance\Balance;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'contract_id' => ['sometimes', 'required', 'numeric', 'exists:contracts,id'],
            'teacher_id' => ['sometimes', 'required', 'numeric', 'exists:teachers,id'],
            'year' => ['sometimes', 'required', 'numeric', 'min:2015'],
            'split' => ['sometimes', 'bool'],
        ]);

        if ($request->has('contract_id')) {
            $contract = Contract::find($request->contract_id);
            $balance = $contract->getBalance();
        } else {
            $teacher = Teacher::find($request->teacher_id);
            if ($request->has('available_years')) {
                return Balance::getAvailableYears($teacher);
            }
            $balance = $teacher->getBalance(
                $request->year,
                $request->has('split') ? (bool) $request->split : null
            );
        }

        return paginate($balance->groupByDay());
    }
}

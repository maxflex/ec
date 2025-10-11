<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContractBalanceResource;
use App\Models\Contract;
use Illuminate\Http\Request;

/**
 * Сводный баланс всех договоров
 * /contract-balances
 */
class ContractBalanceController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'year' => ['required', 'numeric', 'min:2015'],
        ]);

        $contracts = Contract::query()
            ->with(['payments', 'client'])
            ->withCount(['comments' => fn ($q) => $q->where('extra', 'contract-balances')])
            ->where('year', $request->year)
            ->get()
            ->sortBy(['client.last_name', 'client.first_name', 'client.middle_name'])
            ->values()
            ->all();

        return paginate(ContractBalanceResource::collection($contracts));
    }
}

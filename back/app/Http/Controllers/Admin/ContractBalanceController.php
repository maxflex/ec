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
            'year' => ['required', 'numeric', 'min:2015']
        ]);

        $contracts = Contract::query()
            ->where('year', $request->year)
            ->get();

        return paginate(ContractBalanceResource::collection($contracts));
    }
}
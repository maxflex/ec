<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Direction;
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
            'direction' => ['sometimes', 'array'],
        ]);

        $query = Contract::query()
            ->with(['payments', 'client'])
            ->with('versions', fn ($q) => $q->where('is_active', true))
            ->withCount('comments')
            ->where('year', $request->year);

        if ($request->has('direction') && count($request->direction)) {
            $programs = collect();
            foreach ($request->direction as $directionString) {
                $programs = $programs->concat(
                    Direction::from($directionString)->toPrograms()
                );
            }

            $query->whereHas('versions', fn ($q) => $q
                ->where('is_active', true)
                ->whereHas('programs', fn ($q) => $q->whereIn('program', $programs->unique()->values()))
            );
        }

        $contracts = $query
            ->get()
            ->sortBy(['client.last_name', 'client.first_name', 'client.middle_name'])
            ->values()
            ->all();

        return paginate(ContractBalanceResource::collection($contracts));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SwampListResource;
use App\Models\Contract;
use App\Models\ContractVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SwampController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'program'],
        'status' => ['status'],
        'client' => ['client_id'],
    ];

    public function index(Request $request)
    {
        $lastVersionsCte = ContractVersion::query()
            ->selectRaw(<<<SQL
                contract_id as last_contract_id,
                MAX(version) as last_version
            SQL)->groupBy('contract_id');

        /**
         * client_groups и groups
         */
        $gcgCte = DB::table('client_groups', 'gc')
            ->select('gc.contract_id', 'gc.group_id', 'g.program', 'g.year')
            ->join('groups as g', 'g.id', '=', 'gc.group_id');

        /**
         * Программы последней версии договора
         */
        $cvpCte = DB::table('contract_version_programs', 'cvp')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join(
                'lv',
                fn ($join) => $join
                    ->on('cv.contract_id', '=', 'lv.last_contract_id')
                    ->on('cv.version', '=', 'lv.last_version')
            )
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->leftJoin('gcg', fn ($join) => $join
                ->on('gcg.contract_id', '=', 'c.id')
                ->on('gcg.program', '=', 'cvp.program')
            )
            ->selectRaw(<<<SQL
                cvp.id,
                cvp.id as `cvp_id`,
                cast(prices->>'$[0][0]' as unsigned) as `lessons`,
                `group_id`,
                cv.contract_id,
                c.year,
                cvp.program,
                `is_closed`
            SQL);

        /**
         * В группе, но нет соответствующей программы в последней версии договора
         */
        $noContractInGroupCte = DB::table('gcg')
            ->leftJoin(
                'cvp',
                fn ($join) => $join
                    ->on('gcg.contract_id', '=', 'cvp.contract_id')
                    ->on('gcg.program', '=', 'cvp.program')
                    ->on('gcg.year', '=', 'cvp.year'),
            )
            ->whereNull('cvp.id') // нет программы
            ->selectRaw(<<<SQL
                UUID() as `id`,
                `cvp_id`,
                `lessons`,
                gcg.group_id,
                gcg.contract_id,
                gcg.year,
                gcg.program,
               `is_closed`
            SQL);

        $cvpQuery = DB::table('cvp');
        $noContractInGroupQuery = DB::table('noContractInGroup');

        $this->filter($request, $cvpQuery);
        $this->filter($request, $noContractInGroupQuery);

        $cvpQuery->withExpression('lv', $lastVersionsCte)
            ->withExpression('gcg', $gcgCte)
            ->withExpression('cvp', $cvpCte)
            ->withExpression('noContractInGroup', $noContractInGroupCte)
            ->union($noContractInGroupQuery);

        // $query->where('contract_id', 14740);
        return $this->handleIndexRequest($request, $cvpQuery, SwampListResource::class);
    }

    protected function filterStatus(&$query, $status)
    {
        switch ($status) {
            case 'toFulfil':
                $query
                    ->whereNull('group_id')
                    ->where('is_closed', false);
                break;
            case 'closedInGroup':
                $query
                    ->where('is_closed', true)
                    ->whereNotNull('group_id');
                break;
            case 'noContractInGroup':
                $query
                    ->whereNotNull('group_id')
                    ->whereNull('cvp_id');
                break;
        }
    }

    protected function filterClient(&$query, $clientId)
    {
        $contractIds = Contract::query()->where('client_id', $clientId)->pluck('id');
        $query->whereIn('contract_id', $contractIds);
    }
}

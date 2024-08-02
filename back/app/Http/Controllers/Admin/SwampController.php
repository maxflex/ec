<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SwampListResource;
use App\Models\ContractVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SwampController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'program'],
        'status' => ['status']
    ];

    public function index(Request $request)
    {
        $lastVersionsQuery =  ContractVersion::selectRaw(<<<SQL
            contract_id as last_contract_id,
            MAX(version) as last_version
        SQL)->groupBy('contract_id');

        /**
         * Программы последней версии договора
         */
        $cvpQuery = DB::table('contract_version_programs', 'cvp')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join(
                'lv',
                fn ($join) => $join
                    ->on('cv.contract_id', '=', 'lv.last_contract_id')
                    ->on('cv.version', '=', 'lv.last_version')
            )
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->leftJoin('group_contracts as gc', 'gc.contract_id', '=', 'c.id')
            ->leftJoin(
                'groups as g',
                fn ($join) => $join
                    ->on('g.id', '=', 'gc.group_id')
                    ->on('g.program', '=', 'cvp.program')
            )
            ->selectRaw(<<<SQL
                cvp.id,
                cvp.id as `cvp_id`,
                g.id as `group_id`,
                cv.contract_id,
                c.year,
                cvp.program,
                `is_closed`
            SQL);

        /**
         * В группе, но нет соответствующей программы в последней версии договора
         */
        $groupContractsQuery = DB::table('group_contracts', 'gc')
            ->join('groups as g', 'g.id', '=', 'gc.group_id')
            ->leftJoin(
                'cvp',
                fn ($join) => $join
                    ->on('gc.contract_id', '=', 'cvp.contract_id')
                    ->on('g.program', '=', 'cvp.program')
                    ->on('g.year', '=', 'cvp.year'),
            )
            ->whereNull('cvp.id')
            ->selectRaw(<<<SQL
                UUID() as `id`,
                NULL as `cvp_id`,
                gc.group_id,
                gc.contract_id,
                g.year,
                g.program,
               false as `is_closed`
            SQL);

        $q1 = DB::table('cvp');
        $q2 = DB::table('gc');

        $this->filter($request, $q1);
        $this->filter($request, $q2);

        $q1->withExpression('lv', $lastVersionsQuery)
            ->withExpression('cvp', $cvpQuery)
            ->withExpression('gc', $groupContractsQuery)
            ->union($q2);

        // $query->where('contract_id', 14740);
        return $this->handleIndexRequest($request, $q1, SwampListResource::class);
    }

    protected function filterStatus(&$query, $status)
    {
        switch ($status) {
            case 'toFullfill':
                return $query
                    ->whereNull('group_id')
                    ->where('is_closed', false);
            case 'closedInGroup':
                return $query
                    ->where('is_closed', true)
                    ->whereNotNull('group_id');
            case 'noContractInGroup':
                return $query
                    ->whereNotNull('group_id')
                    ->whereNull('cvp_id');
        }
    }
}

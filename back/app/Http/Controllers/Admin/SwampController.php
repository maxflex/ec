<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SwampListResource;
use App\Models\Contract;
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
        /**
         * Программы последней версии договора
         */
        $swampsCte = DB::table('contract_version_programs', 'cvp')
            ->join('contract_versions as cv', fn($join) => $join
                ->on('cv.id', '=', 'cvp.contract_version_id')
                ->where('cv.is_active', true)
            )
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->leftJoin(
                'client_groups as cg',
                'cg.contract_version_program_id',
                '=',
                'cvp.id'
            )
            ->selectRaw(<<<SQL
                cvp.id,
                cast(prices->>'$[0][0]' as unsigned) as `lessons`,
                `group_id`,
                cv.contract_id,
                c.year,
                cvp.program,
                `is_closed`
            SQL);

        $query = DB::table('swamps')->withExpression('swamps', $swampsCte);
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, SwampListResource::class);
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

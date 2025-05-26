<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SwampListResource;
use App\Models\ContractVersionProgram;
use Illuminate\Http\Request;

class SwampController extends Controller
{
    protected $filters = [
        'equals' => [
            'year', 'program', 'status', 'client_id',
        ],
        'noGroup' => ['no_group'],
    ];

    public function index(Request $request)
    {
        $query = ContractVersionProgram::with(['prices', 'clientGroup'])
            ->join('contract_versions as cv', fn ($join) => $join
                ->on('cv.id', '=', 'contract_version_programs.contract_version_id')
                ->where('cv.is_active', true)
            )
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->selectRaw('
                contract_version_programs.*,
                c.year,
                c.client_id,
                cv.contract_id
            ');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, SwampListResource::class);
    }

    protected function filterNoGroup($query)
    {
        $query->whereDoesntHave('clientGroup');
    }
}

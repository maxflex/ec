<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SwampStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PersonResource;
use App\Http\Resources\SwampListResource;
use App\Models\Client;
use App\Models\ContractVersionProgram;
use Illuminate\Http\Request;

class SwampController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'client_id'],
        'findInSet' => ['program'],
    ];

    public function index(Request $request)
    {
        $query = ContractVersionProgram::with(['prices', 'clientGroup.group'])
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

        if ($request->has('counts')) {
            return $this->counts($query);
        }

        return $this->handleIndexRequest($request, $query, SwampListResource::class);
    }

    private function counts($query)
    {
        $data = $query->get()->groupBy('client_id');
        $result = collect();
        foreach ($data as $clientId => $d) {
            $counts = [];
            foreach (SwampStatus::cases() as $status) {
                $counts[$status->value] = $d->where('status', $status->value)->count();
            }
            $result->push([
                'client' => new PersonResource(Client::find($clientId)),
                'counts' => $counts,
            ]);
        }

        return paginate($result);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CvpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PersonResource;
use App\Http\Resources\SwampListResource;
use App\Models\Client;
use App\Models\ContractVersionProgram;
use App\Models\Group;
use Illuminate\Http\Request;

class SwampController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'client_id'],
        'findInSet' => ['program'],
    ];

    public function index(Request $request)
    {
        $query = ContractVersionProgram::with([
            'prices',
            'clientGroup.group',
            'contractVersion.contract.client',
        ])
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

        if ($request->has('by_program')) {
            return $this->byProgram($query);
        }

        return $this->handleIndexRequest($request, $query, SwampListResource::class);
    }

    private function counts($query)
    {
        $data = $query->get()->groupBy('client_id');
        $result = collect();
        foreach ($data as $clientId => $d) {
            $counts = [];
            foreach (CvpStatus::cases() as $status) {
                $inGroup = 0;
                $noGroup = 0;
                foreach ($d->where('status', $status->value)->values() as $e) {
                    if ($e->clientGroup) {
                        $inGroup++;
                    } else {
                        $noGroup++;
                    }
                }

                $counts[$status->value.'_in_group'] = $inGroup;
                $counts[$status->value.'_no_group'] = $noGroup;
            }
            $result->push([
                'client' => new PersonResource(Client::find($clientId)),
                'counts' => $counts,
            ]);
        }

        return paginate($result);
    }

    /**
     * Болота по программе /swamps/by-program
     */
    private function byProgram($query)
    {
        $data = $query->get()->groupBy('program');
        $result = collect();
        foreach ($data as $program => $d) {
            $counts = [
                'groups' => Group::query()
                    ->where('year', request()->input('year'))
                    ->where('program', $program)
                    ->count(),
            ];
            foreach (CvpStatus::cases() as $status) {
                $inGroup = 0;
                $noGroup = 0;
                foreach ($d->where('status', $status->value)->values() as $e) {
                    if ($e->clientGroup) {
                        $inGroup++;
                    } else {
                        $noGroup++;
                    }
                }

                $counts[$status->value.'_in_group'] = $inGroup;
                $counts[$status->value.'_no_group'] = $noGroup;
            }
            $result->push([
                'program' => $program,
                'counts' => $counts,
            ]);
        }

        return paginate($result);
    }
}

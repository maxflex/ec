<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SwampListResource;
use App\Utils\Swamp;
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
        $query = Swamp::query();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, SwampListResource::class);
    }

    protected function filterStatus(&$query, $status)
    {
        switch ($status) {
            case 'toFulfil':
                $query->whereRaw("group_id IS NULL AND lessons_passed < lessons");
                break;
            case 'fulfilled':
                $query->whereRaw("group_id IS NULL AND lessons_passed >= lessons");
                break;
            case 'fulfilledInGroup':
                $query->whereRaw("group_id IS NOT NULL AND lessons_passed >= lessons");
                break;
        }
    }

    protected function filterClient(&$query, $clientId)
    {
        $programIds = DB::table('contract_version_programs', 'cvp')
            ->join('contract_versions as cv', fn($join) => $join
                ->on('cv.contract_version_program', '=', 'cvp.id')
                ->where('cv.is_active', true)
            )
            ->join('contracts as c', fn($join) => $join->on('c.id', '=', 'cv.contract_id'))
            ->where('c.year', request()->year())
            ->where('c.client_id', $clientId)
            ->pluck('cvp.id');

        $query->whereIn('id', $programIds);
    }
}

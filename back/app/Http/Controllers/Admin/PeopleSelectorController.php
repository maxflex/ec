<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonResource;
use App\Utils\Swamp;
use Illuminate\Http\Request;

class PeopleSelectorController extends Controller
{
    protected $filters = [
        'equals' => [
            'year', 'program', 'client_id', 'group_id'
        ],
        'status' => ['status'],
    ];

    public function __invoke(Request $request)
    {
        return $request->input('mode') === 'clients'
            ? $this->clients($request)
            : $this->teachers($request);
    }

    private function clients(Request $request)
    {
        $query = Swamp::query();
        $this->filter($request, $query);

        $clientsQ = (clone $query)
            ->selectRaw("c.id, c.last_name, c.first_name, c.middle_name")
            ->join('clients as c', 'c.id', '=', 'client_id')
            ->groupBy('c.id')
            ->orderByRaw("c.last_name, c.first_name, c.middle_name");

        $result = PersonResource::collection((clone $clientsQ)->paginate(30));

        if (intval($request->page) === 1) {
            $result->additional([
                'extra' => [
                    'ids' => $clientsQ->pluck('c.id')->all(),
                    'group_ids' => $query
                        ->whereNotNull('group_id')
                        ->groupBy('group_id')
                        ->select('group_id')
                        ->pluck('group_id')
                        ->all(),
                ]
            ]);
        }

        return $result;
    }

    private function teachers(Request $request)
    {
        $query = Swamp::query();
        $this->filter($request, $query);
        return 123;
    }


    protected function filterStatus($query, $statuses)
    {
        $query->where(function ($q) use ($statuses) {
            foreach ($statuses as $status) {
                switch ($status) {
                    case 'toFulfil':
                        $q->orWhereRaw("group_id IS NULL AND total_price_passed < total_price");
                        break;

                    case 'exceedNoGroup':
                        $q->orWhereRaw("group_id IS NULL AND total_price_passed > total_price");
                        break;

                    case 'completeNoGroup':
                        $q->orWhereRaw("group_id IS NULL AND total_price_passed = total_price");
                        break;

                    case 'inProcess':
                        $q->orWhereRaw("group_id IS NOT NULL AND total_price_passed < total_price");
                        break;

                    case 'exceedInGroup':
                        $q->orWhereRaw("group_id IS NOT NULL AND total_price_passed > total_price");
                        break;

                    case 'completeInGroup':
                        $q->orWhereRaw("group_id IS NOT NULL AND total_price_passed = total_price");
                        break;
                }
            }
        });
    }
}

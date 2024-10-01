<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonResource;
use App\Models\Teacher;
use App\Utils\Swamp;
use Illuminate\Http\Request;

class PeopleSelectorController extends Controller
{
    protected $clientFilters = [
        'equals' => [
            'year', 'program', 'client_id', 'group_id'
        ],
        'statuses' => ['statuses'],
    ];

    protected $teacherFilters = [
        'equals' => ['status'],
        'findInSet' => ['subjects'],
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

        $groupsQ = (clone $query)
            ->whereNotNull('group_id')
            ->orderBy('group_id')
            ->groupBy('group_id');

        $this->filter($request, $query, $this->clientFilters);

        $this->filter(new Request($request->except('group_id')), $groupsQ, $this->clientFilters);

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
                    'group_ids' => $groupsQ->pluck('group_id')->all(),
                ]
            ]);
        }

        return $result;
    }

    private function teachers(Request $request)
    {
        $query = Teacher::query()
            ->withPayments($request->year)
            ->orderByRaw("last_name, first_name, middle_name");

        $this->filter($request, $query, $this->teacherFilters);

        $result = PersonResource::collection((clone $query)->paginate(30));

        if (intval($request->page) === 1) {
            $result->additional([
                'extra' => [
                    'ids' => $query->pluck('teachers.id')->all(),
//                    'group_ids' => $groupsQ->pluck('group_id')->all(),
                ]
            ]);
        }

        return $result;
    }


    protected function filterStatuses($query, $statuses)
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

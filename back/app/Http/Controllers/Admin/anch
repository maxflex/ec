<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractVersion;
use App\Models\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    protected $filters = [
        'equals' => ['year']
    ];

    public function index(Request $request)
    {
        return [
            'data' => $this->getData($request),
            'meta' => [
                'total' => 999,
                'current_page' => intval($request->page),
                'last_page' => 999,
            ]
        ];
    }

    private function getData(Request $request)
    {
        $mode = $request->mode;
        $page = intval($request->page);
        $paginate = intval($request->paginate);

        // 1st page = -0 day
        $from = now()->modify(sprintf('-%d %s', ($page - 1) * $paginate, $mode));
        if ($mode === 'week') {
            $from->startOfWeek();
        }
        $to = (clone $from)->modify(sprintf("-%d %s", $paginate - 1, $mode));


        switch ($request->mode) {
            case 'day':
                // return $this->getByDayHardcoded($page, $paginate, $request);
                $format = 'Y-m-d';
                $sqlFormat = '%Y-%m-%d';
                break;
                // case 'week':
                //     $format = 'Y-m-d';
                //     $sqlFormat = 'LEFT(DATE_ADD(%1$s, INTERVAL(-WEEKDAY(%1$s)) DAY), 10)';
                //     break;
            case 'month':
                $format = 'Y-m-01';
                $sqlFormat = '%Y-%m-01';
                break;
            case 'year':
                $format = 'Y-01-01';
                $sqlFormat = '%Y-01-01';
                break;
        }

        /**
         * from : "2024-06-14"
         * to : "2024-04-26"
         */

        $result = [];
        while ($from >= $to) {
            $date = $from->format($format);
            $result[] = [
                'date' => $date,
                ...$this->getRequestsData($date, $sqlFormat),
                ...$this->getContractsData($date, $sqlFormat),
            ];
            $from->modify("-1 $mode");
        }
        return $result;
    }

    private function getRequestsData($date, $sqlFormat)
    {
        $query = ClientRequest::whereRaw(
            "DATE_FORMAT(created_at, ?) = ?",
            [
                $sqlFormat,
                $date
            ]
        );

        return [
            'requests_count' => $query->count()
        ];
    }

    private function getContractsData($date, $sqlFormat)
    {
        $query = ContractVersion::query()
            ->whereRaw(
                "DATE_FORMAT(created_at, ?) = ?",
                [
                    $sqlFormat,
                    $date
                ]
            );

        $contractVersions = $query->get();

        $result = (object) [
            'new_contracts_count' => 0,
            'new_contracts_sum' => 0,
            'new_programs_count' => 0,
            'programs_added_count' => 0,
            'programs_removed_count' => 0,
            'contracts_sum_change' => 0
        ];

        foreach ($contractVersions as $current) {
            if ($current->version === 1) {
                $result->new_contracts_count++;
                $result->new_contracts_sum += $current->sum;
                $result->new_programs_count += $current->programs()->count();
            } else {
                $previous = $current->previous;
                $currentPrograms = $current
                    ->programs()
                    ->active()
                    ->pluck("program")
                    ->map(fn ($p) => $p->name);
                $previousPrograms = $previous
                    ->programs()
                    ->active()
                    ->pluck("program")
                    ->map(fn ($p) => $p->name);
                $result->programs_added_count += $currentPrograms->diff($previousPrograms)->count();
                $result->programs_removed_count += $previousPrograms->diff($currentPrograms)->count();
                $result->contracts_sum_change += ($current->sum - $previous->sum);
            }
        }

        return (array) $result;
    }
}

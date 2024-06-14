<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
                $fn = "date_format(%s, '%%Y-%%m-%%d')";
                break;
                // case 'week':
                //     $format = 'Y-m-d';
                //     $fn = 'LEFT(DATE_ADD(%1$s, INTERVAL(-WEEKDAY(%1$s)) DAY), 10)';
                //     break;
            case 'month':
                $format = 'Y-m-01';
                $fn = "date_format(%s, '%%Y-%%m-01')";
                break;
            case 'year':
                $format = 'Y-01-01';
                $fn = "date_format(%s, '%%Y-01-01')";
                break;
        }


        return DB::select(sprintf(
            <<<SQL
        with recursive `dates` (`date`) as (
            select '%s'
            union
            select `date` - interval 1 %s
            from dates
            where `date` > '%s'
        )
        select d.date,
        (
            select count(*) from `requests` as r
            where d.date = %s
        ) as requests_count,
        cv_data.contracts_count,
        CAST(cv_data.contracts_sum as UNSIGNED) as contracts_sum
        from `dates` as d
        LEFT JOIN
        (
            SELECT
                %s as `datee`,
                COUNT(*) AS contracts_count,
                SUM(cv.sum) AS contracts_sum
            FROM
                `contract_versions` AS cv
            WHERE
                cv.version = 1
            GROUP BY
                %s
        ) AS cv_data ON cv_data.datee = d.date
        SQL,
            $from->format($format),
            $mode,
            $to->format($format),
            sprintf($fn, 'r.created_at'),
            sprintf($fn, 'cv.created_at'),
            sprintf($fn, 'cv.created_at'),
        ));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StatsExport;
use App\Http\Controllers\Controller;
use App\Utils\Stats\Stats;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StatsController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'mode' => ['required'],
            'page' => ['nullable', 'numeric'],
            'metrics' => ['required', 'array'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
        ]);

        // если дата не установлена, то сегодняшняя
        $date = $request->date ?? now()->format('Y-m-d');

        // если date_from не установлена, то "год назад"
        $dateFrom = $request->input('date_from') ?? now()->subYear()->addDay()->format('Y-m-d');

        $result = Stats::getData(
            $request->mode,
            $date,
            $dateFrom,
            $request->metrics,
            $request->page,
        );

        // если page не указан, то экспортируем
        if ($request->page === null) {
            $export = new StatsExport($result['data'], $request->metrics);
            return Excel::download($export, 'stats.xlsx');
        }

        return [
            'data' => $result['data'],
            'is_last_page' => $result['is_last_page'],
            'totals' => $request->page === 1 ? Stats::getTotals(
                $date,
                $dateFrom,
                $request->metrics,
            ) : null,
        ];
    }
}

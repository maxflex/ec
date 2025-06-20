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
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ]);
        // если дата не установлена, то сегодняшняя
        $dateToStr = $request->date_to ?? now()->format('Y-m-d');

        // если date_from не установлена, то "год назад"
        $dateFromStr = $request->date_from ?? now()->subYear()->addDay()->format('Y-m-d');

        $result = Stats::get(
            $request->mode,
            $dateFromStr,
            $dateToStr,
            $request->metrics,
            $request->page,
        );

        // если page не указан, то экспортируем
        if ($request->page === null) {
            $export = new StatsExport($result['data'], $request->metrics);

            return Excel::download($export, 'stats.xlsx');
        }

        return $result;
    }
}

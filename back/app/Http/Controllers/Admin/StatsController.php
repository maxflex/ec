<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Utils\Stats\Stats;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'mode' => ['required'],
            'page' => ['required'],
            'metrics' => ['required'],
            'date' => ['nullable', 'date_format:Y-m-d']
        ]);

        // если дата не установлена, то сегодняшняя
        $date = $request->date ?? now()->format('Y-m-d');

        return Stats::getData(
            $request->mode,
            $date,
            $request->page,
            $request->metrics,
        );
    }
}

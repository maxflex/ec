<?php

namespace App\Http\Controllers\Client;

use App\Enums\Quarter;
use App\Http\Resources\QuartersGradesSimpleResource;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends \App\Http\Controllers\Admin\GradeController
{
    public function index(Request $request)
    {
        $request->merge([
            'client_id' => auth()->id(),
        ]);

        $query = Grade::query();
        $this->filter($request, $query);

        $grades = $query->get();

        // отфильтруй
        $today = now()->startOfDay();

        $grades = $grades
            ->filter(function (Grade $grade) use ($today) {
                $quarter = Quarter::from($grade->quarter);

                $limitDate = $quarter->limitDateForYear($grade->year);

                // если лимита нет — показываем всегда
                if (! $limitDate) {
                    return true;
                }

                logger('limit date: '.$limitDate, $grade->toArray());
                logger('', [
                    'result' => $today->gte($limitDate),
                ]);

                // показываем только если сегодня >= лимита
                return $today->gte($limitDate);
            })
            ->values(); // сбросить ключи

        // отфильтруй
        if ($request->has('available_years')) {
            return $grades->pluck('year')->unique()->sortDesc()->values()->all();
        }

        // ГРУППИРУЕМ по (year, program)
        $grouped = $grades->groupBy(fn (Grade $g) => $g->year.'-'.$g->program);

        return paginate(QuartersGradesSimpleResource::collection($grouped));
    }
}

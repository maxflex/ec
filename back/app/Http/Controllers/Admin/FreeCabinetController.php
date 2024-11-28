<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Cabinet;
use App\Enums\LessonStatus;
use App\Http\Controllers\Controller;
use App\Models\Lesson;

class FreeCabinetController extends Controller
{
    public function __invoke()
    {
        $date = now()->format('Y-m-d');
        $time = now()->format('H:i:s');

        $cabinets = collect(Cabinet::cases())
            ->filter(fn($e) => str($e->value)->startsWith('cab'))
            ->map(function ($cabinet) use ($date, $time) {
                $query = Lesson::where('date', $date)
                    ->where('cabinet', $cabinet)
                    ->where('status', '<>', LessonStatus::cancelled);

                // время, до которого кабинет свободен
                $freeUntil = (clone $query)
                    ->where('time', '>', $time)
                    ->min('time');

                // кабинет занят
                $isBusy = (clone $query)
                    ->join('groups as g', 'g.id', '=', 'lessons.group_id')
                    ->where('time', '<', $time)
                    ->whereRaw("
                        NOW() < `time` + INTERVAL IF(g.program LIKE '%School8' OR g.program LIKE '%School9', 55, 125) MINUTE
                    ")
                    ->exists();
                return [
                    'cabinet' => $cabinet,
                    'free_until' => $freeUntil,
                    'is_busy' => $isBusy,
                ];
            })
            ->sortBy([
                ['is_busy', 'asc'],
                ['free_until', 'desc']
            ])
            ->values()
            ->all();

        return paginate($cabinets);
    }
}

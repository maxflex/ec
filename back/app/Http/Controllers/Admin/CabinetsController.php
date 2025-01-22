<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Cabinet;
use App\Enums\LessonStatus;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Utils\Teeth;

class CabinetsController extends Controller
{
    public function __invoke()
    {
        $result = [];
        foreach (Cabinet::cases() as $cabinet) {
            if (str($cabinet->value)->startsWith('tur')) {
                continue;
            }
            $query = Lesson::query()->where('cabinet', $cabinet);
            $result[] = [
                'cabinet' => $cabinet,
                'teeth' => Teeth::get($query, current_academic_year()),
                ...$this->getFreeCabinetData($cabinet)
            ];
        }

        return paginate($result);
    }

    /**
     * Свободные кабинеты
     */
    private function getFreeCabinetData(Cabinet $cabinet)
    {
        $date = now()->format('Y-m-d');
        $time = now()->format('H:i:s');

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
            'free_until' => $freeUntil,
            'is_busy' => $isBusy,
        ];
    }
}

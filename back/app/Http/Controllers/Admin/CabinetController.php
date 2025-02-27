<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Cabinet;
use App\Enums\LessonStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PersonResource;
use App\Models\Group;
use App\Models\Lesson;
use App\Utils\Teeth;
use Illuminate\Http\Request;

class CabinetController extends Controller
{
    public function index()
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
                ...$this->getFreeCabinetData($cabinet),
            ];
        }

        return paginate($result);
    }

    /**
     * Свободные кабинеты на общей странице
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
        $busyBy = (clone $query)
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->where('time', '<', $time)
            ->whereRaw("
                NOW() < `time` + INTERVAL IF(g.program LIKE '%School8' OR g.program LIKE '%School9', 55, 125) MINUTE
            ")
            ->first();

        return [
            'free_until' => $freeUntil,
            'busy_by' => $busyBy ? extract_fields($busyBy, [
                'program',
            ], [
                'teacher' => new PersonResource($busyBy->teacher),
            ]) : null,
        ];
    }

    /**
     * Свободные кабинеты при добавлении нового занятия
     */
    public function free(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'group_id' => ['required', 'exists:groups,id'],
        ]);

        $date = $request->input('date');
        $time = $request->input('time').':00';
        $group = Group::find($request->input('group_id'));
        $duration = $group->program->getDuration();
        $endTime = date('H:i:s', strtotime($time) + ($duration * 60));

        $result = collect();

        foreach (Cabinet::cases() as $cabinet) {
            if (str($cabinet->value)->startsWith('tur')) {
                continue;
            }

            // Проверяем, есть ли занятия, которые пересекаются с новым
            $isBusy = Lesson::where('date', $date)
                ->where('cabinet', $cabinet)
                ->where('status', LessonStatus::planned)
                ->join('groups as g', 'g.id', '=', 'lessons.group_id')
                ->whereRaw("(
                    (`time` BETWEEN ? AND ?)
                    OR
                    (`time` + INTERVAL IF(g.program LIKE '%School8' OR g.program LIKE '%School9', 55, 125) MINUTE BETWEEN ? AND ?)
                )", [$time, $endTime, $time, $endTime])
                ->exists();

            $result->push([
                'cabinet' => $cabinet->value,
                'is_busy' => $isBusy,
            ]);
        }

        return $result->sortBy('is_busy')->values()->all();
    }
}

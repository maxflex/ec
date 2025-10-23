<?php

namespace App\Utils;

use App\Enums\LessonStatus;
use App\Enums\TeacherPaymentMethod;
use App\Http\Resources\PersonResource;
use App\Models\Lesson;
use App\Models\Report;
use App\Models\Teacher;
use App\Models\TeacherPayment;
use App\Models\TeacherService;

class TeacherBalance
{
    public static function getData($year): array
    {
        $queries = [
            (object) [
                'name' => 'lessons_planned',
                'query' => Lesson::query()
                    ->where('status', LessonStatus::planned)
                    ->join('groups as g', 'g.id', '=', 'lessons.group_id'),
                'sum' => 'price',
            ],
            (object) [
                'name' => 'lessons_conducted',
                'query' => Lesson::query()
                    ->where('status', LessonStatus::conducted)
                    ->join('groups as g', 'g.id', '=', 'lessons.group_id'),
                'sum' => 'price',
            ],
            (object) [
                'query' => Report::query(),
                'sum' => 'price',
            ],
            (object) [
                'name' => 'paid_lessons',
                'query' => TeacherPayment::where('is_confirmed', true),
                'sum' => 'sum',
            ],
            (object) [
                'name' => 'paid_other',
                'query' => TeacherPayment::where('is_confirmed', true),
                'sum' => 'sum',
            ],
            (object) [
                'query' => TeacherService::query(),
                'sum' => 'sum',
            ],
        ];

        $result = collect();
        foreach (Teacher::withPayments($year)->get() as $teacher) {
            $resultItem = [
                'teacher' => new PersonResource($teacher),
            ];
            foreach ($queries as $q) {
                $query = $q->query->clone();

                if (! isset($q->table)) {
                    $q->table = $q->name ?? $query->getModel()->getTable();
                }

                if (@$q->name === 'paid_lessons') {
                    if ($teacher->is_split_balance) {
                        $query->where('method', TeacherPaymentMethod::bill);
                    } else {
                        $query->where('id', -1);
                    }
                }

                if (@$q->name === 'paid_other') {
                    if ($teacher->is_split_balance) {
                        $query->where('method', '<>', TeacherPaymentMethod::bill);
                    }
                }

                $value = $query
                    ->where('year', $year)
                    ->where('teacher_id', $teacher->id)
                    ->sum($q->sum);

                $resultItem[$q->table] = intval($value);
            }

            $total = $resultItem['lessons_conducted'] + $resultItem['reports'] + $resultItem['teacher_services'];
            $totalLessons = $teacher->is_split_balance ? $resultItem['lessons_conducted'] : 0;
            $totalOther = $teacher->is_split_balance ? $resultItem['reports'] + $resultItem['teacher_services'] : $total;

            $resultItem = [
                ...$resultItem,
                'total' => $total,
                'to_pay_lessons' => $totalLessons - $resultItem['paid_lessons'],
                'to_pay_other' => $totalOther - $resultItem['paid_other'],
            ];
            $result->push($resultItem);
        }

        return $result->sortBy([
            'teacher.last_name', 'teacher.first_name', 'teacher.middle_name',
        ])->values()->all();
    }
}

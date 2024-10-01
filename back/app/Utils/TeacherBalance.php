<?php

namespace App\Utils;

use App\Enums\LessonStatus;
use App\Http\Resources\PersonResource;
use App\Models\{Lesson, Report, Teacher, TeacherPayment, TeacherService};

class TeacherBalance
{
    public static function getData($year): array
    {
        $queries = [
            (object)[
                'name' => 'lessons_planned',
                'query' => Lesson::query()
                    ->where('status', LessonStatus::planned)
                    ->join('groups as g', 'g.id', '=', 'lessons.group_id'),
                'sum' => 'price',
            ],
            (object)[
                'name' => 'lessons_conducted',
                'query' => Lesson::query()
                    ->where('status', LessonStatus::conducted)
                    ->join('groups as g', 'g.id', '=', 'lessons.group_id'),
                'sum' => 'price',
            ],
            (object)[
                'query' => Report::query(),
                'sum' => 'price'
            ],
            (object)[
                'query' => TeacherPayment::query(),
                'sum' => 'sum',
            ],
            (object)[
                'query' => TeacherService::query(),
                'sum' => 'sum'
            ]
        ];

        $result = collect();
        foreach (Teacher::withPayments($year)->get() as $teacher) {
            $resultItem = [
                'teacher' => new PersonResource($teacher)
            ];
            foreach ($queries as $q) {
                if (!isset($q->table)) {
                    $q->table = $q->name ?? $q->query->getModel()->getTable();
                }

                $value = $q->query
                    ->clone()
                    ->where('year', $year)
                    ->where('teacher_id', $teacher->id)
                    ->sum($q->sum);

                $resultItem[$q->table] = intval($value);
            }
            $total = $resultItem['lessons_conducted'] + $resultItem['reports'] + $resultItem['teacher_services'];
            $resultItem = [
                ...$resultItem,
                'total' => $total,
                'to_pay' => $total - $resultItem['teacher_payments']
            ];
            $result->push($resultItem);
        }

        return $result->sortBy([
            'teacher.last_name', 'teacher.first_name', 'teacher.middle_name'
        ])->values()->all();
    }
}
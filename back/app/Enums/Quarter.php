<?php

namespace App\Enums;

use Illuminate\Support\Carbon;

enum Quarter: string
{
    case q1 = 'q1';
    case q2 = 'q2';
    case q3 = 'q3';
    case q4 = 'q4';
    case final = 'final';

    /**
     * HARDCODE: до этих дат у учеников/представителей не должны нигде
     * отображаться четвертные оценки
     */
    public function limitDateForYear(int $year): ?Carbon
    {
        $limits = [
            self::q2->value => [
                2025 => '2025-12-19',
            ],
        ];

        $date = $limits[$this->value][$year] ?? null;

        return $date
            ? Carbon::parse($date)->startOfDay()
            : null; // null = нет ограничения
    }
}

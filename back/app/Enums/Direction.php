<?php

namespace App\Enums;

use Illuminate\Http\Request;

enum Direction: string
{
    case courses9 = 'courses9';
    case courses10 = 'courses10';
    case courses11 = 'courses11';
    case school8 = 'school8';
    case school9 = 'school9';
    case school10 = 'school10';
    case school11 = 'school11';
    case external = 'external';
    case practicum = 'practicum';
    case egeTrial = 'egeTrial';
    case python = 'python';
    case english = 'english';
    case online = 'online';

    /**
     * Получить направления по заданным программам
     *
     * @param Program[] $programs
     * @return self[]
     */
    public static function fromPrograms(array $programs): array
    {
        $directions = [];

        // Проходим по каждой программе и определяем ее направление
        foreach ($programs as $program) {
            $direction = self::fromProgram($program);

            if ($direction && !in_array($direction, $directions)) {
                $directions[] = $direction;
            }
        }

        return $directions;
    }

    public static function fromProgram(Program $program): ?Direction
    {
        $str = str($program->value);

        return match (true) {
            $str->endsWith('External') => Direction::external,
            $str->endsWith('School8') => Direction::school8,
            $str->endsWith('School9') => Direction::school9,
            $str->endsWith('Oge') => Direction::school9,
            $str->endsWith('School10') => Direction::school10,
            $str->endsWith('School11') => Direction::school11,
            $str->endsWith('Practicum') => Direction::practicum,
            $str->endsWith('9') => Direction::courses9,
            $str->endsWith('10') => Direction::courses10,
            $str->endsWith('11') => Direction::courses11,
            default => null,
        };
    }

    /**
     * Получить направление входящей заявке
     * TODO: в идеале переделать фронт, чтобы отправлял значение напрямую
     */
    public static function fromIncomingRequest(Request $request): Direction
    {
        $grade = intval($request->input('grade'));

        /**
         * forms: [
         * { value: "courses", text: "Курсы" },
         * { value: "external", text: "Экстернат" },
         * { value: "school", text: "Старшая школа" },
         * { value: "other", text: "Развивающие курсы" },
         * ],
         */
        switch ($request->input('form')) {
            case 'courses':
                return match ($grade) {
                    9 => Direction::courses9,
                    10 => Direction::courses10,
                    default => Direction::courses11
                };

            case 'external':
                return Direction::external;

            case 'school':
                return match ($grade) {
                    8 => Direction::school8,
                    9 => Direction::school9,
                    10 => Direction::school10,
                    default => Direction::school11
                };

            case 'other':
                return match ($request->input('otherCourse')) {
                    'python' => Direction::python,
                    default => Direction::english
                };
        }

        // не удалось получить направление
        return Direction::online;
    }
}
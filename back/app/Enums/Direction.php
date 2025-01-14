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
    case coursesExtra = 'coursesExtra';

    /**
     * Получить направление входящей заявки
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

    /**
     * Из направления получить массив программ
     *
     * @return array<string>
     *
     * TODO: fix!
     */
    public static function toPrograms(Direction $direction): array
    {
//        return collect(Program::cases())
//            ->filter(fn($p) => $p->getExam() === $this->exam)
//            ->all();
        $programs = collect(Program::cases())->map(fn($e) => str($e->name));

        $result = match ($direction) {
            self::courses9 => $programs->filter(fn($p) => $p->length() < 7 && $p->endsWith("9")),
            self::courses10 => $programs->filter(fn($p) => $p->length() < 7 && $p->endsWith("10")),
            self::courses11 => $programs->filter(fn($p) => $p->length() < 7 && $p->endsWith("11")),
            self::school8 => $programs->filter(fn($p) => $p->endsWith("School8")),
            self::school9 => $programs->filter(fn($p) => $p->endsWith("School9")),
            self::school10 => $programs->filter(fn($p) => $p->endsWith("School10")),
            self::school11 => $programs->filter(fn($p) => $p->endsWith("School11")),
            self::external => $programs->filter(fn($p) => $p->endsWith("External")),
            self::practicum => $programs->filter(fn($p) => $p->contains("Pract")),
            default => collect(),
        };

        return $result->values()->all();
    }
}
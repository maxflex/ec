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
    case coursesExtra = 'coursesExtra';

    /**
     * Получить направление входящей заявки
     * TODO: в идеале переделать фронт, чтобы отправлял значение напрямую
     */
    public static function fromIncomingRequest(Request $request): ?Direction
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
        return match ($request->input('form')) {
            'courses' => match ($grade) {
                9 => Direction::courses9,
                10 => Direction::courses10,
                default => Direction::courses11
            },
            'external' => Direction::external,
            'school' => match ($grade) {
                8 => Direction::school8,
                9 => Direction::school9,
                10 => Direction::school10,
                default => Direction::school11
            },
            null => null, // форма обучения может быть не указана
            default => Direction::coursesExtra,
        };
    }

    /**
     * Из направления получить массив программ
     *
     * @return Program[]
     */
    public function toPrograms(): array
    {
        return collect(Program::cases())
            ->filter(fn($p) => $p->getDirection() === $this)
            ->values()
            ->all();
    }
}
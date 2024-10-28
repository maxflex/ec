<?php

namespace App\Enums;

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
            $str->endsWith('Ext') => Direction::external,
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
}
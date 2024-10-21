<?php

namespace App\Enums;

enum RequestDirection: string
{
    case courses9 = 'courses9';
    case courses10 = 'courses10';
    case courses11 = 'courses11';
    case school8 = 'school8';
    case school9 = 'school9';
    case school10 = 'school10';
    case school11 = 'school11';
    case external = 'external';
    case otherPython = 'otherPython';
    case otherEnglish = 'otherEnglish';

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
            $str = str($program->value);

            $direction = match (true) {
                $str->endsWith('Ext') => RequestDirection::external,
                $str->endsWith('School8') => RequestDirection::school8,
                $str->endsWith('School9') => RequestDirection::school9,
                $str->endsWith('School10') => RequestDirection::school10,
                $str->endsWith('School11') => RequestDirection::school11,
                $str->endsWith('9') => RequestDirection::courses9,
                $str->endsWith('10') => RequestDirection::courses10,
                $str->endsWith('11') => RequestDirection::courses11,
                default => null,
            };

            if ($direction && !in_array($direction, $directions)) {
                $directions[] = $direction;
            }
        }

        return $directions;
    }
}
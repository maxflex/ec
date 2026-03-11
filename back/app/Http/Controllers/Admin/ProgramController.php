<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Program;
use App\Http\Controllers\Controller;

/**
 * Дебаг-страница админская для отображения программ
 */
class ProgramController extends Controller
{
    public function __invoke(): array
    {
        // Собираем весь срез данных, который можно получить из Program enum.
        $programs = collect(Program::cases())
            ->map(function (Program $program) {
                $direction = $program->getDirection();
                $exam = $program->getExam();

                return [
                    'value' => $program->value,
                    'name' => $program->getName(),
                    'human_name' => $program->getHumanName(),
                    'short_name' => $program->getShortName(),
                    'direction_value' => $direction->value,
                    'direction_name' => $direction->getName(),
                    'exam_value' => $exam?->value,
                    'exam_name' => $exam?->getName(),
                    'duration' => $program->getDuration(),
                ];
            })
            ->values();

        return paginate($programs->all(), [
            'total' => $programs->count(),
        ]);
    }
}

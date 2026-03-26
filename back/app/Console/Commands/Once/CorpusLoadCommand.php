<?php

namespace App\Console\Commands\Once;

use App\Enums\Cabinet;
use App\Enums\ClientLessonStatus;
use App\Models\ClientLesson;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CorpusLoadCommand extends Command
{
    private const CABINETS = [
        Cabinet::cab407->value,
        Cabinet::cab408->value,
        Cabinet::cab409->value,
        Cabinet::cab412->value,
        Cabinet::cab413->value,
        Cabinet::cab414->value,
        Cabinet::cab416->value,
        Cabinet::cab417->value,
        Cabinet::cab418->value,
        Cabinet::cab420->value,
        Cabinet::cab422->value,
        Cabinet::cab423->value,
        Cabinet::cab424->value,
        Cabinet::cab427->value,
        Cabinet::cab428->value,
        Cabinet::cab430->value,
        Cabinet::cab432->value,
        Cabinet::cab433->value,
        Cabinet::cab434->value,
        Cabinet::cab307->value,
        Cabinet::cab308->value,
        Cabinet::cab310->value,
        Cabinet::cab312->value,
        Cabinet::cab314->value,
        Cabinet::cab316->value,

        // Новые
        Cabinet::cab512a->value,
        Cabinet::cab509->value,
        Cabinet::cab520->value,
        Cabinet::cab521->value,
        Cabinet::cab519->value,
        Cabinet::cab514->value,
    ];

    private const TIMES = [
        '09:00',
        '09:30',
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '12:00',
        '12:30',
        '13:00',
        '13:30',
        '14:00',
        '14:30',
        '15:00',
        '15:30',
        '16:00',
        '16:30',
        '17:00',
        '17:30',
        '18:00',
        '18:30',
        '19:00',
        '19:30',
        '20:00',
        '20:30',
        '21:00',
    ];

    protected $signature = 'once:corpus-load {--dist}';

    protected $description = 'Create TSV with campus load per timeslot based on client lessons';

    public function handle(): void
    {
        $startDate = Carbon::create(2025, 9, 1)->startOfDay();
        $endDate = Carbon::today();

        $clientLessons = ClientLesson::query()
            ->when($this->option('dist'), fn ($q) => $q->whereIn('status', [
                ClientLessonStatus::present->value,
                ClientLessonStatus::late->value,
            ]))
            ->whereHas('lesson', function ($query) use ($startDate, $endDate) {
                $query
                    ->whereBetween('date', [
                        $startDate->format('Y-m-d'),
                        $endDate->format('Y-m-d'),
                    ])
                    ->whereIn('cabinet', self::CABINETS);
            })
            ->with([
                'lesson.group',
                'contractVersionProgram.contractVersion.contract',
            ])
            ->get();

        $rangesByDateAndCabinet = [];

        foreach ($clientLessons as $clientLesson) {
            $lesson = $clientLesson->lesson;

            $contractVersionProgram = $clientLesson->contractVersionProgram;

            $clientId = $contractVersionProgram->contractVersion->contract->client_id;

            $date = $lesson->date;
            $cabinet = $lesson->cabinet;
            $lessonStart = $lesson->date_time->copy();
            $lessonEnd = $lesson->date_time->copy()->addMinutes($lesson->group->program->getDuration());

            // Отдельно считаем интервалы по каждому кабинету:
            // иначе студент попадал в общий счёт по дате без привязки к кабинету.
            $rangesByDateAndCabinet[$date] ??= [];
            $rangesByDateAndCabinet[$date][$cabinet] ??= [];
            $rangesByDateAndCabinet[$date][$cabinet][$clientId] ??= [
                'start' => $lessonStart,
                'end' => $lessonEnd,
            ];

            if ($lessonStart->lt($rangesByDateAndCabinet[$date][$cabinet][$clientId]['start'])) {
                $rangesByDateAndCabinet[$date][$cabinet][$clientId]['start'] = $lessonStart;
            }

            if ($lessonEnd->gt($rangesByDateAndCabinet[$date][$cabinet][$clientId]['end'])) {
                $rangesByDateAndCabinet[$date][$cabinet][$clientId]['end'] = $lessonEnd;
            }
        }

        $result = [
            ['datetime', ...self::CABINETS],
        ];

        foreach (date_range($startDate->toDateString(), $endDate->toDateString()) as $date) {
            $dateString = $date->format('Y-m-d');

            foreach (self::TIMES as $time) {
                $slot = Carbon::parse(sprintf('%s %s', $dateString, $time));
                $row = [sprintf('%s %s', $dateString, $time)];

                foreach (self::CABINETS as $cabinet) {
                    $count = 0;

                    foreach ($rangesByDateAndCabinet[$dateString][$cabinet] ?? [] as $range) {
                        if ($range['start']->lte($slot) && $range['end']->gt($slot)) {
                            $count++;
                        }
                    }

                    $row[] = $count;
                }

                $result[] = $row;
            }
        }

        $url = save_csv($result);

        $this->line($url);
    }
}

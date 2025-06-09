<?php

namespace App\Console\Commands\Once;

use App\Models\Lesson;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ClientLessonCountsCommand extends Command
{
    protected $signature = 'once:client-lesson-counts';

    protected $description = 'Create CSV lesson - client_lesson counts';

    public function handle(): void
    {
        $lessons = Lesson::query()
            ->whereHas('group', fn ($q) => $q->where('year', 2024))
            ->with('clientLessons', 'group')
            ->get()
            ->groupBy('date');

        $counts = range(1, 12);
        $times = [
            '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00',
            '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30',
        ];
        $headers = ['дата', ...$counts];
        $result = [$headers];

        foreach (date_range('2024-09-01', '2025-06-30') as $date) {
            $d = $date->format('Y-m-d');

            foreach ($times as $time) {
                $datetime = Carbon::parse("$d $time");
                $row = ["$d $time"];

                if (! isset($lessons[$d])) {
                    $row = array_pad($row, 1 + count($counts), 0);
                } else {
                    $matchingLessons = $lessons[$d]->filter(fn (Lesson $lesson) => $lesson->date_time->lte($datetime) &&
                        Carbon::parse($lesson->time_end)->gt($datetime)
                    );

                    foreach ($counts as $i) {
                        $row[] = $matchingLessons
                            ->where(fn (Lesson $l) => $l->clientLessons->count() === $i)
                            ->count();
                    }
                }

                $result[] = $row;
            }
        }

        $url = save_csv($result);

        $this->line($url);
    }
}

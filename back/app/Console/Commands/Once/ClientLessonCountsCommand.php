<?php

namespace App\Console\Commands\Once;

use App\Models\Lesson;
use Illuminate\Console\Command;

class ClientLessonCountsCommand extends Command
{
    protected $signature = 'once:client-lesson-counts';

    protected $description = 'Create CSV lesson - client_lesson counts';

    public function handle(): void
    {
        $lessons = Lesson::query()
            ->whereHas('group', fn ($q) => $q->where('year', 2024))
            ->with('clientLessons')
            ->get()
            ->groupBy('date');

        $counts = range(1, 12);
        $headers = ['дата', ...$counts];
        $result = [$headers];

        foreach (date_range('2024-09-01', '2025-06-30') as $date) {
            $d = $date->format('Y-m-d');
            $item = [$d];
            foreach ($counts as $i) {
                if (! isset($lessons[$d])) {
                    $item[] = 0;
                } else {
                    $item[] = $lessons[$d]->where(fn (Lesson $l) => $l->clientLessons->count() === $i)->count();
                }
            }
            $result[] = $item;
        }

        $url = save_csv($result);

        $this->line($url);
    }
}

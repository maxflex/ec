<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Models\Teacher;
use App\Utils\TeacherStats;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TeacherStatsCommand extends Command
{
    // минимальный год в статистике
    const START_YEAR = 2017;

    protected $signature = 'app:teacher-stats {--all} {--only-avg}';

    protected $description = 'Recalculate teacher stats';

    public function handle(): void
    {
        if (! $this->option('only-avg')) {
            $this->calculateForTeachers();
        }
        $this->calculateAvg();
    }

    private function calculateForTeachers()
    {
        if ($this->option('all')) {
            $years = range(self::START_YEAR, current_academic_year());
            DB::table('teachers')->update([
                'stats' => null,
            ]);
        } else {
            $years = [current_academic_year()];
        }

        $this->info('Calculating stats for each teacher...');

        foreach ($years as $i => $year) {
            $this->info("$year year (".($i + 1).'/'.count($years).')');
            $teacherIds = Lesson::query()
                ->join('groups as g', 'g.id', '=', 'lessons.group_id')
                ->where('g.year', $year)
                ->whereNotNull('lessons.teacher_id')
                ->when(is_localhost(), fn ($q) => $q->where('teacher_id', '<=', 23))
                ->distinct()
                ->pluck('lessons.teacher_id');

            $bar = $this->output->createProgressBar(count($teacherIds));
            foreach (Teacher::whereIn('id', $teacherIds)->get() as $teacher) {
                $stats = $teacher->stats ?? [];
                $stats[$year] = (new TeacherStats($teacher, $year))->get();
                $teacher->stats = $stats;
                $teacher->save();
                $bar->advance();
            }
            $bar->finish();
            $this->info("\n");
        }

        $this->info('Teachers stats calculated.');
    }

    private function calculateAvg()
    {
        $this->info('Updating average stats...');

        $teachers = Teacher::whereNotNull('stats')->get();

        $years = range(self::START_YEAR, current_academic_year());
        $result = [];

        foreach ($years as $i => $year) {
            $this->info("$year year (".($i + 1).'/'.count($years).')');
            $bar = $this->output->createProgressBar($teachers->count());

            $yearlySums = [];
            $yearlyCounts = [];

            foreach ($teachers as $teacher) {
                $stats = $teacher->stats[$year] ?? null;

                if (! $stats) {
                    $bar->advance();

                    continue;
                }

                foreach ($stats as $key => $value) {
                    if ($key === 'lessons') {
                        continue;
                    }

                    if (! isset($yearlySums[$key])) {
                        $yearlySums[$key] = 0;
                        $yearlyCounts[$key] = 0;
                    }

                    if ($value) {
                        $yearlySums[$key] += $value;
                        $yearlyCounts[$key]++;
                    }
                }

                $bar->advance();
            }
            $bar->finish();
            $this->info("\n");

            $averages = [];
            foreach ($yearlySums as $key => $total) {
                $averages[$key] = $yearlyCounts[$key] > 0
                    ? round($total / $yearlyCounts[$key], is_float($total) ? 2 : 0)
                    : 0;
            }

            $result[$year] = $averages;
        }

        // Save the new avg stats back to the "average teacher"
        TeacherStats::saveAvg($result);

        $this->info('Average stats updated.');
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Models\Teacher;
use App\Utils\TeacherStats;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TeacherStatsCommand extends Command
{
    protected $signature = 'app:teacher-stats {--all}';

    protected $description = 'Recalculate teacher stats';

    /** @var array<int, int> */
    private array $years;

    public function handle(): void
    {
        if ($this->option('all')) {
            $this->years = range(2015, current_academic_year());
            DB::table('teachers')->update([
                'stats' => null,
            ]);
        } else {
            $this->years = [current_academic_year()];
        }

        $this->calculateForTeachers();
        $this->calculateAvg();
    }

    private function calculateForTeachers()
    {
        $this->info('Calculating stats for each teacher...');

        foreach ($this->years as $i => $year) {
            $this->info("$year year (".($i + 1).'/'.count($this->years).')');
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

        $result = [];

        foreach ($this->years as $i => $year) {
            $this->info("$year year (".($i + 1).'/'.count($this->years).')');
            $bar = $this->output->createProgressBar($teachers->count());

            $yearlySums = [];
            $teachersWithYearData = 0;

            foreach ($teachers as $teacher) {
                $stats = $teacher->stats[$year] ?? null;

                if (! $stats) {
                    $bar->advance();

                    continue;
                }

                $teachersWithYearData++;

                foreach ($stats as $key => $value) {
                    if ($key === 'lessons') {
                        continue;
                    }

                    if (! isset($yearlySums[$key])) {
                        $yearlySums[$key] = 0;
                    }

                    $yearlySums[$key] += $value;
                }

                $bar->advance();
            }
            $bar->finish();
            $this->info("\n");

            if ($teachersWithYearData > 0) {
                $averages = [];
                foreach ($yearlySums as $key => $total) {
                    $averages[$key] = round($total / $teachersWithYearData, is_float($total) ? 2 : 0);
                }

                $result[$year] = $averages;
            }
        }

        // Save the new avg stats back to the "average teacher"
        TeacherStats::saveAvg($result);

        $this->info('Average stats updated.');
    }
}

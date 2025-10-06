<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Models\Teacher;
use App\Utils\TeacherStatsNew;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TeacherStatsNewCommand extends Command
{
    // минимальный год в статистике
    const START_YEAR = 2017;

    protected $signature = 'app:teacher-stats-new {--all} {--only-avg}';

    protected $description = 'Recalculate teacher stats';

    public function handle(): void
    {
        $this->updateTeacherStats();
    }

    private function updateTeacherStats()
    {
        // $years = range(self::START_YEAR, current_academic_year());
        $years = [current_academic_year()];
        DB::table('teachers')->update([
            'stats_new' => null,
        ]);

        $this->info('Calculating stats for each teacher...');

        foreach ($years as $i => $year) {
            $this->info("$year year (".($i + 1).'/'.count($years).')');
            $teacherIds = Lesson::query()
                ->join('groups as g', 'g.id', '=', 'lessons.group_id')
                ->where('g.year', $year)
                ->whereNotNull('lessons.teacher_id')
                // ->when(is_localhost(), fn ($q) => $q->where('teacher_id', '<=', 23))
                ->distinct()
                ->pluck('lessons.teacher_id');

            $bar = $this->output->createProgressBar(count($teacherIds));
            foreach (Teacher::whereIn('id', $teacherIds)->get() as $teacher) {
                $statsNew = $teacher->stats_new ?? [];
                $statsNew[$year] = (new TeacherStatsNew($teacher, $year))->get();
                $teacher->stats_new = $statsNew;
                $teacher->save();
                $bar->advance();
            }
            $bar->finish();
            $this->info("\n");
        }

        $this->info('Teachers stats calculated.');
    }
}

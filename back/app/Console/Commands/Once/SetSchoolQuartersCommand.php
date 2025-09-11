<?php

namespace App\Console\Commands\Once;

use App\Enums\Direction;
use App\Enums\Program;
use App\Enums\Quarter;
use App\Models\Lesson;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetSchoolQuartersCommand extends Command
{
    protected $signature = 'once:set-school-quarters';

    protected $description = 'Установить четверти для занятия школа (разово)';

    public function handle(): void
    {
        $school8 = collect(Program::cases())->filter(fn (Program $p) => $p->getDirection() === Direction::school8);
        $school9 = collect(Program::cases())->filter(fn (Program $p) => $p->getDirection() === Direction::school9);

        $allPrograms = $school8->merge($school9)->all();

        $lessons = Lesson::query()
            ->with('group')
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->where('g.year', current_academic_year())
            ->whereIn('g.program', $allPrograms)
            ->orderBy('date')
            ->get();

        $bar = $this->output->createProgressBar($lessons->count());

        foreach ($lessons as $lesson) {
            $date = $lesson->date;

            $quarter = null;

            if ($date >= '2025-09-08' && $date <= '2025-10-31') {
                $quarter = Quarter::q1;
            } elseif ($date >= '2025-11-01' && $date <= '2025-12-31') {
                $quarter = Quarter::q2;
            } elseif ($date >= '2026-01-01' && $date <= '2026-03-13') {
                $quarter = Quarter::q3;
            } else {
                $quarter = Quarter::q4;
            }

            DB::table('lessons')->whereId($lesson->id)->update([
                'quarter' => $quarter->value,
            ]);

            $bar->advance();
        }
        $bar->finish();
    }
}

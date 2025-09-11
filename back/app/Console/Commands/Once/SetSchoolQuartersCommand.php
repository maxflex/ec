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
        $programs = collect();
        foreach (Program::cases() as $program) {
            if (in_array($program->getDirection(), [
                Direction::school8,
                Direction::school9,
            ])) {
                if (! str($program->value)->contains('Oge')) {
                    $programs->push($program->value);
                }
            }
        }

        dd($programs->map(fn ($e) => "'".$e."'")->join(','));

        $lessons = Lesson::query()
            ->selectRaw('lessons.id, lessons.date')
            ->with('group')
            ->join('groups as g', fn ($join) => $join
                ->on('g.id', '=', 'lessons.group_id')
                ->where('g.year', 2025)
                ->whereIn('g.program', $programs)
            )
            ->orderBy('lessons.date')
            ->get();

        $bar = $this->output->createProgressBar($lessons->count());

        foreach ($lessons as $lesson) {
            $date = $lesson->date;

            $quarter = null;

            if ($date <= '2025-10-31') {
                $quarter = Quarter::q1;
            } elseif ($date <= '2025-12-31') {
                $quarter = Quarter::q2;
            } elseif ($date <= '2026-03-13') {
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

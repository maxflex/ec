<?php

namespace App\Console\Commands\Once;

use App\Models\Grade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixGradesCommand extends Command
{
    protected $signature = 'once:fix-grades';

    protected $description = 'Command description';

    public function handle(): void
    {
        $grades = Grade::all();
        $bar = $this->output->createProgressBar($grades->count());
        foreach ($grades as $grade) {
            $teacherId = DB::table('client_lessons', 'cl')
                ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
                ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
                ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
                ->join('lessons as l', 'l.id', '=', 'cl.lesson_id')
                ->join('groups as g', 'g.id', '=', 'l.group_id')
                ->where('g.year', $grade->year)
                ->where('c.client_id', $grade->client_id)
                ->where('cvp.program', $grade->program)
                ->where('l.quarter', $grade->quarter)
                ->orderByRaw('l.date desc, l.time desc')
                ->value('teacher_id');

            if (!$teacherId) {
                throw new \Exception('no teacher id');
            }

            DB::table('grades')->whereId($grade->id)->update([
                'teacher_id' => $teacherId
            ]);

            $bar->advance();
        }
        $bar->finish();
    }
}

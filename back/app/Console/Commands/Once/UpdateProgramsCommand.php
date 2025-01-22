<?php

namespace App\Console\Commands\Once;

use App\Enums\Program;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateProgramsCommand extends Command
{
    protected $signature = 'once:update-programs';

    protected $description = 'Command description';

    const TABLES = [
        'groups',
        'client_reviews',
        'client_tests',
        'tests',
        'reports',
        'grades',
        'scholarship_scores',
        'contract_version_programs'
    ];

    public function handle(): void
    {
        $this->updateProgram('engSpokenOther', Program::engSpoken->value);

        $practicumPrograms = str(
            'mathPracticumPract11,socPracticumPract11,rusPracticumPract11,hisPracticumPract11,litPracticumPract11,physPracticumPract11,engPracticumPract11,bioPracticumPract11,mathPracticum11,rusPracticumOther,rusPract11,bioPracticum11,chemPracticumPract11,bioPracticumOther'
        )->explode(',');

        foreach ($practicumPrograms as $p) {
            // mathPracticumPract11 => math
            $subject = str($p)->before('Pract')->value();
            $newProgram = $subject . "Pract";
            $this->updateProgram($p, $newProgram);
        }

        // -----

        $this->updateProgram('soch10', Program::soch11->value);
    }

    private function updateProgram(string $oldProgram, string $newProgram)
    {
        foreach (self::TABLES as $table) {
            DB::table($table)
                ->where('program_new', $oldProgram)
                ->update([
                    'program_new' => $newProgram
                ]);
        }
    }
}

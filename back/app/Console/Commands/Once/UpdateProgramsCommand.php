<?php

namespace App\Console\Commands\Once;

use App\Enums\Program;
use App\Models\Group;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
//        $this->updateDbStructure();
        $this->mathBaseAndProfSplitUp();
    }

    /**
     * Изначальная очистка программ, когда их было 500+
     */
    private function cleanUp500()
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

    /**
     * Разбить математику на базу и профиль в школа-10, школа-11, экстернат
     */
    private function mathBaseAndProfSplitUp()
    {
        $this->info(PHP_EOL . 'mathBaseAndProfSplitUp');

        // группы, которые нужно перевести в мат-профиль
        $groups = Group::whereIn('program', [
            Program::mathBaseSchool10,
            Program::mathBaseSchool11,
            Program::mathBaseExternal,
        ])
            // исключить мат база
            ->whereNotIn('id', [
                595, 264, 404, 294, 1501, 1253, 1500, 1499, 1498, 1495, 1494, 1493, 1471, 1470, 1469,
                1256, 1603, 1533, 1534, 1590, 1604, 1608, 1629, 1630, 1749, 1318, 1496, 1581, 1588, 903,
                403, 584, 585, 765, 780, 898, 899, 900, 901, 902, 1247, 1109, 1110, 1111, 1112, 1113, 1188,
                1189, 1244, 1245, 1246, 763, 764, 1181
            ])
            ->get();

        $bar = $this->output->createProgressBar(count($groups));

        foreach ($groups as $g) {
            $newProgram = match ($g->program) {
                Program::mathBaseSchool10 => Program::mathProfSchool10,
                Program::mathBaseSchool11 => Program::mathProfSchool11,
                Program::mathBaseExternal => Program::mathProfExternal,
            };

            DB::table('groups')->whereId($g->id)->update([
                'program' => $newProgram->value
            ]);

            $ids = DB::table('lessons', 'l')
                ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
                ->where('l.group_id', $g->id)
                ->pluck('cl.contract_version_program_id')
                ->unique();

//            $this->line($g->id . ": " . $ids->join(', '));

            DB::table('contract_version_programs')
                ->whereIn('id', $ids)
                ->update([
                    'program' => $newProgram->value
                ]);

            $bar->advance();
        }
        $bar->finish();
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

    private function updateDbStructure()
    {
        $this->info("Updating DB structure...");
        $bar = $this->output->createProgressBar(count(self::TABLES));
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('program_new');
            });

            DB::table($table)->update([
                'program_new' => DB::raw('program')
            ]);

            // обновление значений
            DB::table($table)
                ->where('program', 'mathSchool10')
                ->update([
                    'program_new' => Program::mathBaseSchool10->value
                ]);

            DB::table($table)
                ->where('program', 'mathSchool11')
                ->update([
                    'program_new' => Program::mathBaseSchool11->value
                ]);

            DB::table($table)
                ->where('program', 'mathExternal')
                ->update([
                    'program_new' => Program::mathBaseExternal->value
                ]);
            // конец обновление значений

            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('program');
                $table->enum('program', array_column(Program::cases(), 'value'))
                    ->index();
            });

            DB::table($table)->update([
                'program' => DB::raw('program_new')
            ]);

            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('program_new');
            });
            $bar->advance();
        }
        $bar->finish();
    }
}

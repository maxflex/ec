<?php

namespace App\Console\Commands\Once;

use App\Enums\Program;
use App\Models\ContractVersionProgram;
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
        $this->updateReports2();
    }

    private function updateReports2()
    {
        $data = [
            (object)['teacher_id' => 20787, 'client_id' => 6862, 'year' => 2022, 'program' => 'mathProfSchool10', 'new_program' => 'mathBaseSchool10'],
            (object)['teacher_id' => 20787, 'client_id' => 6862, 'year' => 2022, 'program' => 'mathProfSchool10', 'new_program' => 'mathBaseSchool10'],
            (object)['teacher_id' => 20787, 'client_id' => 6862, 'year' => 2022, 'program' => 'mathProfSchool10', 'new_program' => 'mathBaseSchool10'],
            (object)['teacher_id' => 20787, 'client_id' => 6862, 'year' => 2022, 'program' => 'mathProfSchool10', 'new_program' => 'mathBaseSchool10'],
            (object)['teacher_id' => 20787, 'client_id' => 6862, 'year' => 2022, 'program' => 'mathProfSchool10', 'new_program' => 'mathBaseSchool10'],
            (object)['teacher_id' => 20787, 'client_id' => 6862, 'year' => 2022, 'program' => 'mathProfSchool10', 'new_program' => 'mathBaseSchool10'],
            (object)['teacher_id' => 20787, 'client_id' => 6862, 'year' => 2022, 'program' => 'mathProfSchool10', 'new_program' => 'mathBaseSchool10'],
            (object)['teacher_id' => 20787, 'client_id' => 8109, 'year' => 2023, 'program' => 'mathProfExternal', 'new_program' => 'mathBaseExternal'],
        ];

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $d) {
            DB::table('reports')
                ->where('teacher_id', $d->teacher_id)
                ->where('client_id', $d->client_id)
                ->where('year', $d->year)
                ->where('program', $d->program)
                ->update([
                    'program' => $d->new_program
                ]);
            $bar->advance();
        }
        $bar->finish();
    }

    private function updateReports()
    {
        $data = DB::select("
        with d as (
select 
	l.teacher_id, c.client_id, g.year, g.program
from client_lessons cl
join `lessons` l on l.id = cl.lesson_id
join `groups` g on g.id = l.group_id
join contract_version_programs cvp on cvp.id = cl.contract_version_program_id
join contract_versions cv on cv.id = cvp.contract_version_id
join contracts c on c.id = cv.contract_id
group by 
	l.teacher_id, c.client_id, g.year, g.program
),
x as (
select r.teacher_id, r.client_id, r.year, r.program, (
	select count(distinct d.program)
	from d
	where d.teacher_id = r.teacher_id
	and d.client_id = r.client_id
	and d.year = r.year
) as `cnt`
from reports r
where r.program like '%Prof%'
having `cnt` = 1
)
select x.*, (
	select group_concat(d.program)
	from d
	where d.teacher_id = x.teacher_id
	and d.client_id = x.client_id
	and d.year = x.year
) as `new_program`
from x;
        ");

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $d) {
            DB::table('reports')
                ->where('teacher_id', $d->teacher_id)
                ->where('client_id', $d->client_id)
                ->where('year', $d->year)
                ->where('program', $d->program)
                ->update([
                    'program' => $d->new_program
                ]);
            $bar->advance();
        }
        $bar->finish();
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
        // группы, которые нужно перевести в мат-базу
        $groups = Group::query()
            // мат база
            ->whereIn('id', [
                595, 264, 404, 294, 1501, 1253, 1500, 1499, 1498, 1495, 1494, 1493, 1471, 1470, 1469,
                1256, 1603, 1533, 1534, 1590, 1604, 1608, 1629, 1630, 1749, 1318, 1496, 1581, 1588, 903,
                403, 584, 585, 765, 780, 898, 899, 900, 901, 902, 1247, 1109, 1110, 1111, 1112, 1113, 1188,
                1189, 1244, 1245, 1246, 763, 764, 1181
            ])
            ->get();

        $this->info("\n\nClientLessons & ClientGroups...");
        $bar = $this->output->createProgressBar(count($groups));
        $updatedIds = collect();

        foreach ($groups as $g) {
            $newProgram = $this->getNewProgram($g->program);

            DB::table('groups')->whereId($g->id)->update([
                'program' => $newProgram->value
            ]);

            // client_lessons
            $ids = DB::table('lessons', 'l')
                ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
                ->where('l.group_id', $g->id)
                ->pluck('cl.contract_version_program_id')
                ->unique();

            // client_groups
            $ids2 = DB::table('client_groups', 'cg')
                ->where('cg.group_id', $g->id)
                ->pluck('cg.contract_version_program_id')
                ->unique();

            $allIds = $ids->concat($ids2)->unique();
            $updatedIds = $updatedIds->concat($allIds)->unique();

            DB::table('contract_version_programs')
                ->whereIn('id', $allIds)
                ->update([
                    'program' => $newProgram->value
                ]);

            $bar->advance();
        }

        $data = ContractVersionProgram::whereNotIn('id', $updatedIds)
            ->whereIn('program', [
                Program::mathProfSchool10,
                Program::mathProfSchool11,
                Program::mathProfExternal,
            ])
            ->where('lessons_planned', [32, 44])
//            ->whereIn('lessons_planned', [96, 32, 44])
            ->get();


        $this->info("\n\nLeftovers...");
        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $cvp) {
            $newProgram = $this->getNewProgram($cvp->program);

            DB::table('contract_version_programs')
                ->where('id', $cvp->id)
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

    private function getNewProgram(Program $program): Program
    {
        return match ($program) {
            Program::mathProfSchool10 => Program::mathBaseSchool10,
            Program::mathProfSchool11 => Program::mathBaseSchool11,
            Program::mathProfExternal => Program::mathBaseExternal,
            default => throw new \Exception("Cannot get new program from " . $program->value)
        };
    }

    private function updateDbStructure()
    {
        $this->info("\nUpdating DB structure...");
        $bar = $this->output->createProgressBar(count(self::TABLES));
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('program_new');
            });

            DB::table($table)->update([
                'program_new' => DB::raw('program'),
            ]);

            // обновление значений
            DB::table($table)
                ->where('program', 'mathSchool10')
                ->update([
                    'program_new' => Program::mathProfSchool10->value
                ]);

            DB::table($table)
                ->where('program', 'mathSchool11')
                ->update([
                    'program_new' => Program::mathProfSchool11->value
                ]);

            DB::table($table)
                ->where('program', 'mathExternal')
                ->update([
                    'program_new' => Program::mathProfExternal->value
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

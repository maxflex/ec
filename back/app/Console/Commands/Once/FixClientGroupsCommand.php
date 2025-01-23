<?php

namespace App\Console\Commands\Once;

use App\Models\ClientGroup;
use App\Models\ClientLesson;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixClientGroupsCommand extends Command
{
    protected $signature = 'once:fix-client-groups';

    protected $description = 'Command description';

    public function handle(): void
    {
//        Schema::disableForeignKeyConstraints();
//        $this->step1();
//        Schema::enableForeignKeyConstraints();
//        $this->step2();
//        $this->step3();
        $this->step4();
        $this->step5();
    }

    /**
     * Точечно поменяли contract_version_program_id в записях
     * из таблицы client_groups с ID 12306 и 12307
     */
    private function step1()
    {
        DB::transaction(function () {
            $cg12306 = ClientGroup::find(12306);
            $cg12307 = ClientGroup::find(12307);

            $cvpId12306 = $cg12306->contract_version_program_id;
            $cvpId12307 = $cg12307->contract_version_program_id;

            $cg12306->update([
                'contract_version_program_id' => $cvpId12307
            ]);

            $cg12307->update([
                'contract_version_program_id' => $cvpId12306
            ]);

            ClientLesson::where('contract_version_program_id', $cvpId12307)->update([
                'contract_version_program_id' => 0
            ]);

            ClientLesson::where('contract_version_program_id', $cvpId12306)->update([
                'contract_version_program_id' => $cvpId12307
            ]);

            ClientLesson::where('contract_version_program_id', 0)->update([
                'contract_version_program_id' => $cvpId12306
            ]);
        });
    }

    /**
     * Во всех записях из таблицы client_groups, где программы не равны
     * (из groups и из contract_version_programs) – устанавливаем в contract_version_programs
     * программу из groups
     */
    private function step2()
    {
        $data = DB::table('client_groups', 'cg')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cg.contract_version_program_id')
            ->join('groups as g', 'g.id', '=', 'cg.group_id')
            ->whereRaw('g.program <> cvp.program')
            ->selectRaw("
                cvp.id,
                g.program
            ")
            ->get();


        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $d) {
            DB::table('contract_version_programs')
                ->whereId($d->id)
                ->update([
                    'program' => $d->program
                ]);
            $bar->advance();
        }
        $bar->finish();
    }


    /**
     * private 3
     */
    private function step3()
    {
        $data = DB::table('client_lessons', 'cl')
            ->join('lessons as l', 'l.id', '=', 'cl.lesson_id')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->whereIn('cl.contract_version_program_id', [
                27, 189, 4902, 4950, 4951, 6842, 9111, 9500, 9743, 10426, 10894, 11500, 11502, 11977, 11978, 12119, 12145,
                13566, 13713, 14375, 14670, 14741, 14742, 14743, 15225, 15473, 15615, 15623, 15883, 15886, 15969, 16130, 16137,
                16163, 16164, 16169, 16191, 16218, 16224, 16258, 16259, 16260, 16324, 16486, 16571, 16898, 17489, 17685, 17834,
                17956, 19682, 19707, 19708, 19709, 19838, 19857, 19948, 19949, 19950, 19988, 19989, 20098, 20330, 20725, 20815,
                21238, 21434, 21440, 21577, 22106, 22343, 22437, 22443, 22569, 22760, 22762, 23801, 23981, 23982, 24800, 25469,
                25995, 28355, 28569, 28626, 29559, 29579, 29721, 30115, 32148, 32383, 34330, 34393, 34456, 34457, 34458, 34459,
                34481, 35538, 35873, 35878, 36332, 36429, 36741
            ])
            ->selectRaw("
                cl.contract_version_program_id as `id`,
                g.program
            ")
            ->get();


        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $d) {
            DB::table('contract_version_programs')
                ->whereId($d->id)
                ->update([
                    'program' => $d->program
                ]);
            $bar->advance();
        }
        $bar->finish();
    }

    /**
     * Левая таблица из файла Книга2.xls
     * https://doc.ege-centr.ru/tasks/890
     */
    private function step4()
    {
        $this->info("Step 4");
        $data = [
            392032 => 36997,
            392042 => 36997,
            392052 => 36997,
            392062 => 36997,
            392072 => 36997,
            392083 => 36997,
            392094 => 36997,
            392105 => 36997,
            392412 => 36997,
            392422 => 36997,
            392433 => 36997,
            392444 => 36997,
            392455 => 36997,
            392760 => 36997,
            392770 => 36997,
            392781 => 36997,
            392792 => 36997,
            411689 => 37000,
            411694 => 37000,
            411699 => 37000,
            411704 => 37000,
            411709 => 37000,
            411714 => 37000,
            411719 => 37000,
            411732 => 37000,
            411735 => 37000,
            411738 => 37000,
            411806 => 37000,
            411811 => 37000,
            411816 => 37000,
            411821 => 37000,
            411826 => 37000,
            411831 => 37000,
            411848 => 37000,
            411851 => 37000,
            411854 => 37000,
            405106 => 36998,
            405117 => 36998,
            405128 => 36998,
            405139 => 36998,
            405150 => 36998,
            405161 => 36998,
            405172 => 36998,
            405183 => 36998,
            405430 => 36998,
            405441 => 36998,
            405452 => 36998,
            405463 => 36998,
            405474 => 36998,
            405485 => 36998,
            435668 => 36999,
            435675 => 36999,
            435682 => 36999,
            435688 => 36999,
            435696 => 36999,
            435704 => 36999,
            435713 => 36999,
            435722 => 36999,
            435731 => 36999,
            435781 => 36999,
            435789 => 36999,
            435797 => 36999,
            435806 => 36999,
            435815 => 36999,
            435857 => 36999,
            435865 => 36999,
            435873 => 36999,
            435882 => 36999,
            435891 => 36999,
        ];

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $clientLessonId => $cvpId) {
            DB::table('client_lessons')
                ->whereId($clientLessonId)
                ->update([
                    'contract_version_program_id' => $cvpId
                ]);
            $bar->advance();
        }
        $bar->finish();
    }

    /**
     * Фикс ошибки из step1
     */
    private function step5()
    {
        $this->info("Step 5");
        $cvpId1 = 36929;
        $cvpId2 = 36931;

        $data = DB::table('client_lessons')
            ->whereIn('id', [
                463255, 463261, 463267, 463273, 463279, 463284, 463289, 463294, 463299, 463304, 463309,
                463314, 463319, 463324, 463329, 463335, 463341, 463346, 463351, 463356, 463361, 463366,
                463371, 463376, 463381, 463386, 467081, 467087, 470411, 470427, 471717, 471722, 462806,
                462814, 462822, 462831, 462838, 462845, 462852, 462859, 462866, 462873, 462880, 462887,
                462894, 462901, 462909, 462917, 462924, 462931, 462938, 462945, 462952, 462959, 462966,
                462973, 462980, 465727, 467075, 469152, 470367, 471739, 473656
            ])
            ->get();

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $d) {
            DB::table('client_lessons')
                ->whereId($d->id)
                ->update([
                    'contract_version_program_id' => $d->contract_version_program_id === $cvpId1 ? $cvpId2 : $cvpId1,
                ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

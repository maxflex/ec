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
        $this->step2();
        $this->step3();
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
}

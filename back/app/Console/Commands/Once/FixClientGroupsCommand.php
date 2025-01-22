<?php

namespace App\Console\Commands\Once;

use App\Models\ClientGroup;
use App\Models\ClientLesson;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Schema;

class FixClientGroupsCommand extends Command
{
    protected $signature = 'once:fix-client-groups';

    protected $description = 'Command description';

    public function handle(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->step1();
        Schema::enableForeignKeyConstraints();
    }

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
}

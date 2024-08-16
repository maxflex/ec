<?php

namespace App\Console\Commands\Transfer;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferClientGroups extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:client-groups';
    protected $description = 'Transfer client groups';

    public function handle()
    {
        DB::table('client_groups')->delete();
        $items = DB::connection('egecrm')
            ->table('group_contracts', 'gc')
            ->join('groups as g', 'g.id', '=', 'gc.group_id')
            ->selectRaw("gc.*, g.subject_id, g.grade_id")
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $item) {
            try {
                DB::table('client_groups')->insert([
                    'contract_version_program_id' => $this->getContractVersionProgramId(
                        $item->contract_id,
                        $item->grade_id,
                        $item->subject_id
                    ),
                    'group_id' => $item->group_id,
                ]);
            } catch (Exception $e) {
                // бывает unique exception (по одному contract_version_program_id в двух одинаковых group_id)
            }
            $bar->advance();
        }
        $bar->finish();
    }
}

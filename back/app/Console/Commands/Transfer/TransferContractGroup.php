<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferContractGroup extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:contract-group';
    protected $description = 'Transfer stubs';

    public function handle()
    {
        DB::table('contract_group')->delete();
        $items = DB::connection('egecrm')
            ->table('group_contracts')
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $item) {
            DB::table('contract_group')->insert([
                'contract_id' => $item->contract_id,
                'group_id' => $item->group_id,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

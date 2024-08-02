<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferGroupContracts extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:group-contracts';
    protected $description = 'Transfer group contracts';

    public function handle()
    {
        DB::table('group_contracts')->delete();
        $items = DB::connection('egecrm')
            ->table('group_contracts')
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $item) {
            DB::table('group_contracts')->insert([
                'contract_id' => $item->contract_id,
                'group_id' => $item->group_id,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

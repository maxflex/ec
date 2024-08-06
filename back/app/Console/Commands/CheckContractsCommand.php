<?php

namespace App\Console\Commands;

use App\Models\Contract;
use Illuminate\Console\Command;

class CheckContractsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-contracts-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $contracts = Contract::all();

        $result = [];
        $bar = $this->output->createProgressBar($contracts->count());
        foreach ($contracts as $c) {
            $versions = $c->versions;
            if (count($versions) <= 1) {
                $bar->advance();
                continue;
            }
            $prev = null;
            foreach ($versions as $v) {
                if ($prev !== null) {
//                    if ($prev->id > $v->id ) {
//                        $result[] = [
//                            $v->contract_id, $prev->created_at->format('Y-m-d H:i:s'), $v->created_at->format('Y-m-d H:i:s')
//                        ];
//                    }
                    if ($prev->created_at > $v->created_at) {
                        $result[] = [
                            $v->contract_id, $prev->created_at->format('Y-m-d H:i:s'), $v->created_at->format('Y-m-d H:i:s')
                        ];
                    }
                }
                $prev = $v;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info(PHP_EOL);
        dd($result);
    }
}

<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferStubs extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:stubs';
    protected $description = 'Transfer stubs';

    public function handle()
    {
        DB::table('stubs')->delete();
        $stubs = DB::connection('egecrm')
            ->table('stubs')
            ->get();
        $bar = $this->output->createProgressBar($stubs->count());
        foreach ($stubs as $stub) {
            DB::table('stubs')->insert([]);
            $bar->advance();
        }
        $bar->finish();
    }
}

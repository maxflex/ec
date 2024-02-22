<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferVacations extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:vacations';
    protected $description = 'Transfer vacations';

    public function handle()
    {
        DB::table('vacations')->delete();
        $vacations = DB::connection('egecrm')
            ->table('special_dates')
            ->where('type', 'vacation')
            ->get();
        $bar = $this->output->createProgressBar($vacations->count());
        foreach ($vacations as $v) {
            DB::table('vacations')->insert([
                'date' => $v->date
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

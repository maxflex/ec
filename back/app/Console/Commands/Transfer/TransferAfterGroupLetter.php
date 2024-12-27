<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Добавление после основного переноса
 */
class TransferAfterGroupLetter extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:after:group-letter';
    protected $description = 'Transfer zoom';

    public function handle()
    {
        $groups = DB::connection('egecrm')
            ->table('groups')
            ->whereNotNull('letter')
            ->get();
        $bar = $this->output->createProgressBar($groups->count());
        foreach ($groups as $g) {
            DB::table('groups')->whereId($g->id)->update([
                'letter' => $g->letter
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Добавление после основного переноса
 */
class TransferAfterZoom extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:after:zoom';
    protected $description = 'Transfer zoom';

    public function handle()
    {
        $groups = DB::connection('egecrm')
            ->table('groups')
            ->whereNotNull('zoom_id')
            ->get();
        $bar = $this->output->createProgressBar($groups->count());
        foreach ($groups as $g) {
            DB::table('groups')->whereId($g->id)->update([
                'zoom' => $g->zoom_id ? json_encode([
                    'id' => $g->zoom_id,
                    'password' => $g->zoom_password
                ]) : null
            ]);
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();
    }
}

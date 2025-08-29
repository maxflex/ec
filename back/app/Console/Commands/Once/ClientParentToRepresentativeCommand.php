<?php

namespace App\Console\Commands\Once;

use App\Models\Representative;
use DB;
use Illuminate\Console\Command;

class ClientParentToRepresentativeCommand extends Command
{
    protected $signature = 'once:client-parent-to-representative';

    protected $description = 'Command description';

    public function handle(): void
    {
        foreach (['phones', 'telegram_messages'] as $table) {
            DB::table($table)
                ->where('entity_type', 'App\Models\ClientParent')
                ->update([
                    'entity_type' => Representative::class,
                ]);
        }
    }
}

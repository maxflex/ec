<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Добавление после основного переноса
 */
class TransferAfterEmails extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:after:emails';
    protected $description = 'Transfer zoom';

    public function handle()
    {
        $emails = DB::connection('egecrm')
            ->table('emails')
            ->whereNotNull('email')
            ->whereIn('entity_type', [
                self::ET_CLIENT,
                self::ET_PARENT,
            ])
            ->get();
        $bar = $this->output->createProgressBar($emails->count());
        foreach ($emails as $e) {
            if ($e->entity_type === self::ET_CLIENT) {
                DB::table('clients')->whereId($e->entity_id)->update([
                    'email' => $e->email,
                ]);
            } else {
                DB::table('client_parents')->whereId($e->entity_id)->update([
                    'email' => $e->email,
                ]);
            }
            $bar->advance();
        }
        $bar->finish();
    }
}

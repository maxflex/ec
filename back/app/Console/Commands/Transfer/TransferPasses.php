<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferPasses extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:passes';
    protected $description = 'Transfer passes';

    public function handle()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('passes')->truncate();
        $passes = DB::connection('egecrm')
            ->table('passes')
            ->get();
        $bar = $this->output->createProgressBar($passes->count());
        foreach ($passes as $pass) {
            DB::table('passes')->insert([
                'id' => $pass->id,
                'date' => $pass->date,
                'type' => $pass->type,
                'comment' => $pass->comment,
                'request_id' => $pass->request_id,
                'user_id' => $this->getUserId($pass->created_email_id),
                'created_at' => $pass->created_at,
                'updated_at' => $pass->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();


        DB::table('pass_logs')->truncate();
        $passLogs = DB::connection('egecrm')
            ->table('pass_logs')
            ->get();
        $bar = $this->output->createProgressBar($passLogs->count());
        foreach ($passLogs as $passLog) {
            DB::table('pass_logs')->insert([
                'entity_type' => $this->mapEntity($passLog->entity_type),
                'entity_id' => $passLog->entity_id,
                'comment' => $passLog->comment,
                'used_at' => $passLog->used_at,
            ]);
            $bar->advance();
        }
        $bar->finish();

    }
}

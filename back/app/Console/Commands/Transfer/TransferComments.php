<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class TransferComments extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:comments';
    protected $description = 'Transfer teachers';

    public function handle()
    {
        DB::table('comments')->truncate();
        $comments = DB::connection('egecrm')
            ->table('comments')
            ->whereIn('entity_type', [ET_CLIENT, ET_REQUEST])
            ->get();
        $bar = $this->output->createProgressBar($comments->count());
        foreach ($comments as $c) {
            DB::table('comments')->insert([
                'user_id' => $this->getUserId($c->created_email_id),
                'text' => $c->text,
                'entity_id' => $c->entity_id,
                'entity_type' => $this->mapEntity($c->entity_type),
                'created_at' => $c->created_at,
                'updated_at' => $c->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();
    }
}

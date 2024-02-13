<?php

namespace App\Console\Commands\Transfer;

use App\Models\{Teacher, User, Request, Client};
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferPhones extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:phones';
    protected $description = 'Transfer phones';

    public function handle()
    {
        DB::table('phones')->truncate();
        $phones = DB::connection('egecrm')->table('phones')->get();
        $bar = $this->output->createProgressBar($phones->count());
        foreach ($phones as $p) {
            DB::table('phones')
                ->insert([
                    'number' => $p->phone,
                    'comment' => $this->nullify($p->comment),
                    'is_verified' => $p->is_verified,
                    'is_parent' => $p->entity_type === ET_PARENT,
                    'entity_type' => $this->mapEntity($p->entity_type),
                    'entity_id' => $p->entity_id,
                ]);
            $bar->advance();
        }
        $bar->finish();
    }

    private function mapEntity($entityType)
    {
        return match ($entityType) {
            ET_ADMIN => User::class,
            ET_TEACHER => Teacher::class,
            ET_REQUEST => Request::class,
            ET_CLIENT => Client::class,
            ET_PARENT => Client::class,
        };
    }
}

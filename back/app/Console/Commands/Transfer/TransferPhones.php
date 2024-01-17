<?php

namespace App\Console\Commands\Transfer;

use App\Models\{Teacher, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferPhones extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:phones';
    protected $description = 'Transfer phones';

    public function handle()
    {
        $admin = 'App\\Models\\Admin\\Admin';
        DB::table('phones')->truncate();
        $phones = DB::connection('egecrm')
            ->table('phones')
            ->whereIn('entity_type', [
                Teacher::class,
                $admin,
            ])
            ->get();
        $bar = $this->output->createProgressBar($phones->count());
        foreach ($phones as $p) {
            DB::table('phones')
                ->insert([
                    'number' => $p->phone,
                    'comment' => $this->nullify($p->comment),
                    'is_verified' => $p->is_verified,
                    'entity_type' => $p->entity_type === $admin ? User::class : $p->entity_type,
                    'entity_id' => $p->entity_id,
                ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

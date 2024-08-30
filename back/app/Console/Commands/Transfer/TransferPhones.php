<?php

namespace App\Console\Commands\Transfer;

use App\Models\{Request, User};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            $entityType = $this->mapEntity($p->entity_type);
            DB::table('phones')
                ->insert([
                    'number' => $p->phone,
                    'comment' => $this->nullify($p->comment),
                    'is_verified' => $p->is_verified,
                    'entity_type' => $entityType,
                    'entity_id' => $p->entity_id,
                    'telegram_id' => null,
                ]);
            $bar->advance();
        }
        $bar->finish();

        // хардкод доступы
        DB::table('phones')
            ->where('entity_type', User::class)
            ->where('entity_id', 1)
            ->update([
                'number' => '79265056622',
                'telegram_id' => 129886826,
            ]);
        DB::table('phones')
            ->where('number', '79252727210')
            ->where('entity_type', '<>', Request::class)
            ->delete();
        DB::table('phones')->insert([
            'number' => '79252727210',
            'telegram_id' => 84626120,
            'entity_id' => 5,
            'entity_type' => User::class,
        ]);
    }
}

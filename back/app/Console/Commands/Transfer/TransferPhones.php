<?php

namespace App\Console\Commands\Transfer;

use App\Models\{Client, ClientParent, Request, Teacher, User};
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
        $verifiedRequests = [];
        foreach ($phones as $p) {
            $entityType = $this->mapEntity($p->entity_type);
            if ($p->is_verified && $entityType === Request::class) {
                $verifiedRequests[] = $p->entity_id;
            }
            DB::table('phones')
                ->insert([
                    'number' => $p->phone,
                    'comment' => $this->nullify($p->comment),
                    'entity_type' => $entityType,
                    'entity_id' => $p->entity_id,
                    'telegram_id' => null,
                ]);
            $bar->advance();
        }
        $bar->finish();


        // удаляем несуществующие
        foreach ([Client::class, ClientParent::class, Request::class, Teacher::class] as $class) {
            $table = (new $class)->getTable();
            DB::table('phones', 'p')
                ->where('entity_type', $class)
                ->whereRaw("not exists (select 1 from `$table` as x where x.id = p.entity_id)")
                ->delete();
        }

        // is_verified для Requests
        Request::whereIn('id', $verifiedRequests)->update([
            'is_verified' => true
        ]);

        // доступы
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

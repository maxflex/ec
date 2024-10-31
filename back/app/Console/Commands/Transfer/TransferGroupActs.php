<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferGroupActs extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:group-acts';
    protected $description = 'Transfer group acts';

    public function handle()
    {
        DB::table('group_acts')->truncate();
        $items = DB::connection('egecrm')
            ->table('group_acts')
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $item) {
            DB::table('group_acts')->insert([
                'group_id' => $item->group_id,
                'teacher_id' => $item->teacher_id,
                'date' => $item->date,
                'date_from' => $item->date_from,
                'date_to' => $item->date_to,
                'lessons' => $item->lesson_count,
                'sum' => $item->sum,
                'user_id' => $this->getUserId($item->created_email_id),
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

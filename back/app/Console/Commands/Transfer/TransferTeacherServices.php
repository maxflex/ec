<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferTeacherServices extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:teacher-services';
    protected $description = 'Transfer teacher-services';

    public function handle()
    {
        DB::table('teacher_services')->delete();
        $items = DB::connection('egecrm')
            ->table('payment_additionals')
            ->where('entity_type', ET_TEACHER)
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $p) {
            DB::table('teacher_services')->insert([
                'sum' => $p->sum,
                'date' => $p->date,
                'year' => $p->year,
                'purpose' => $p->purpose,
                'teacher_id' => $p->entity_id,
                'user_id' => $this->getUserId($p->created_email_id),
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

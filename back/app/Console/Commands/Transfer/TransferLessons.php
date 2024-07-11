<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Cabinet;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferLessons extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:lessons';
    protected $description = 'Transfer lessons';

    public function handle()
    {
        DB::table('lessons')->delete();
        $lessons = DB::connection('egecrm')
            ->table('lessons')
            ->get();
        $bar = $this->output->createProgressBar($lessons->count());
        foreach ($lessons as $l) {
            DB::table('lessons')->insert([
                'id' => $l->id,
                'group_id' => $l->group_id,
                'teacher_id' => $l->teacher_id,
                'price' => $l->price,
                'cabinet' => Cabinet::getOld($l->cabinet_id)->value,
                'status' => $l->status,
                'date' => $l->date,
                'time' => $l->time,
                'conducted_at' => $l->conducted_at,
                'is_unplanned' => $l->is_unplanned,
                'is_topic_verified' => $l->is_topic_verified,
                'topic' => $l->topic,
                'user_id' => $this->getUserId($l->created_email_id),
                'created_at' => $l->created_at,
                'updated_at' => $l->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Cabinet;
use App\Enums\Quarter;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferLessons extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:lessons';
    protected $description = 'Transfer lessons';

    public function handle()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('lessons')->delete();
        $lessons = DB::connection('egecrm')
            ->table('lessons', 'l')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->selectRaw("
                l.*, 
                if(g.is_free, 1, l.is_free) as is_free,
                g.year
            ")
            ->get();
        $bar = $this->output->createProgressBar($lessons->count());
        foreach ($lessons as $l) {
            DB::table('lessons')->insert([
                'id' => $l->id,
                'group_id' => $l->group_id,
                'teacher_id' => $l->teacher_id,
                'price' => $l->price,
                'cabinet' => Cabinet::fromOld($l->cabinet_id)->value,
                'status' => $l->status,
                'date' => $l->date,
                'time' => $l->time,
                'quarter' => $this->getQuarter($l),
                'conducted_at' => $l->conducted_at,
                'is_free' => $l->is_free,
                'is_unplanned' => $l->is_unplanned,
                'is_topic_verified' => $l->is_topic_verified,
                'topic' => $l->topic,
                'homework' => $l->homework,
                'user_id' => $this->getUserId($l->created_email_id),
                'created_at' => $l->created_at,
                'updated_at' => $l->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();
    }

    private function getQuarter($l): ?Quarter
    {
        $year = intval($l->year);
        if ($year !== 2023) {
            return null;
        }
        $lessonDate = $l->date;
        $nextYear = $year + 1;
        $quarters = [
            "$year-11-15",
            "$year-12-30",
            "$nextYear-03-15",
//            "$nextYear-05-30",
        ];

        // Определяем четверть, в которую попадает дата занятия
        foreach ($quarters as $index => $quarterEndDate) {
            if ($lessonDate <= $quarterEndDate) {
                $q = $index + 1;
                return Quarter::from("q$q");
            }
        }

        return Quarter::q4;
    }
}

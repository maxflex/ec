<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Quarter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Проставляем четверти в 8 класс – школа
 */
class TransferAfterQuarters extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:after:quarters';
    protected $description = 'Transfer zoom';

    public function handle()
    {
        $lessons = DB::connection('egecrm')
            ->table('lessons', 'l')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->selectRaw("
                l.*, 
                if(g.is_free, 1, l.is_free) as is_free,
                g.year, g.grade_id
            ")
            ->where('g.grade_id', 15)
            ->where('g.year', '>=', 2023)
            ->get();

        $bar = $this->output->createProgressBar($lessons->count());
        foreach ($lessons as $l) {
            DB::table('lessons')->whereId($l->id)->update([
                'quarter' => $this->getQuarter($l),
            ]);
            $bar->advance();
        }
        $bar->finish();
    }

    private function getQuarter($l): ?Quarter
    {
        $year = intval($l->year);
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

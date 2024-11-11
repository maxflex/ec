<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use App\Enums\Quarter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferGrades extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:grades';
    protected $description = 'Transfer grades';

    public function handle()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('grades')->truncate();
        $items = DB::connection('egecrm')
            ->table('final_scores as fs')
            ->join('contracts as c', 'c.id', '=', 'fs.contract_id')
            ->selectRaw("
                fs.*, c.client_id, c.grade_id, c.year,
                (
                    select teacher_id from lesson_contracts lc
                    join lessons l on l.id = lc.lesson_id
                    where lc.contract_id = c.id 
                        and l.status = 'conducted'
                        and l.date < CASE 
                            WHEN fs.quarter = 1 THEN '2023-11-15'
                            WHEN fs.quarter = 2 THEN '2023-12-30'
                            WHEN fs.quarter = 3 THEN '2024-03-15'
                            WHEN fs.quarter = 4 THEN '2024-06-30'
                        END
                    order by `date` desc, `time` desc
                    limit 1
                ) as `teacher_id`
            ")
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $item) {
            DB::table('grades')->insert([
                'client_id' => $item->client_id,
                'program' => Program::fromOld($item->grade_id, $item->subject_id),
                'year' => $item->year,
                'quarter' => Quarter::from('q' . $item->quarter),
                'grade' => $item->score,
                'teacher_id' => $item->teacher_id ?? 23,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();
    }
}

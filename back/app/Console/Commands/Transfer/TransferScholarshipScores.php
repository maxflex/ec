<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferScholarshipScores extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:scholarship-scores';
    protected $description = '';

    public function handle()
    {
        DB::table('scholarship_scores')->truncate();
        $items = DB::connection('egecrm')
            ->table('scholarship_scores')
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $item) {
            DB::table('scholarship_scores')->insert([
                'year' => $item->year,
                'month' => $item->month,
                'client_id' => $item->client_id,
                'teacher_id' => $item->teacher_id,
                'program' => Program::fromOld($item->grade_id, $item->subject_id),
                'score' => $item->score,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferReports extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:reports';
    protected $description = 'Transfer reports';

    public function handle()
    {
        DB::table('reports')->delete();
        $items = DB::connection('egecrm')->table('reports')->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $r) {
            $gradeId = min($r->grade_id, 11);
            $gradeId = max($gradeId, 9);
            DB::table('reports')->insert([
                'teacher_id' => $r->teacher_id,
                'client_id' => $r->client_id,
                'year' => $r->year,
                'program' => Program::getBySubjectId($r->subject_id),
                'homework_comment' => $r->homework_comment,
                // 'user_id' => $this->getUserId($r->created_email_id),
                'is_moderated' => ($r->is_not_moderated + 1) % 2,
                'is_published' => $r->is_available_for_parents,
                'price' => $r->price,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

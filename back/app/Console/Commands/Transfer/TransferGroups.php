<?php

namespace App\Console\Commands\Transfer;

use App\Enums\{Subject, Grade};
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferGroups extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:groups';
    protected $description = 'Transfer groups';

    public function handle()
    {
        DB::table('groups')->delete();
        $groups = DB::connection('egecrm')
            ->table('groups')
            ->get();
        $bar = $this->output->createProgressBar($groups->count());
        foreach ($groups as $g) {
            DB::table('groups')->insert([
                'id' => $g->id,
                'teacher_id' => $g->teacher_id,
                'subject' => Subject::getById($g->subject_id)->name,
                'grade' => Grade::getById($g->grade_id)->name,
                'year' => $g->year,
                'is_archived' => $g->is_archived,
                'lessons_planned' => $g->lessons_planned,
                'zoom' => $g->zoom_id ? json_encode([
                    'id' => $g->zoom_id,
                    'password' => $g->zoom_password
                ]) : null,
                'created_at' => $g->updated_at,
                'updated_at' => $g->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class TransferGroups extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:groups';
    protected $description = 'Transfer groups';

    public function handle()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('groups')->delete();
        $groups = DB::connection('egecrm')
            ->table('groups')
            ->selectRaw(<<<SQL
                groups.*, (
                    select duration from lessons
                    where group_id = groups.id
                    limit 1
                ) as duration
            SQL)
            ->get();
        $bar = $this->output->createProgressBar($groups->count());
        foreach ($groups as $g) {
            DB::table('groups')->insert([
                'id' => $g->id,
                'teacher_id' => $g->teacher_id,
                'program' => Program::getById($g->grade_id, $g->subject_id)->name,
                'year' => $g->year,
                'is_archived' => $g->is_archived,
                'zoom' => $g->zoom_id ? json_encode([
                    'id' => $g->zoom_id,
                    'password' => $g->zoom_password
                ]) : null,
                'duration' => $g->duration,
                'created_at' => $g->updated_at,
                'updated_at' => $g->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();
    }
}

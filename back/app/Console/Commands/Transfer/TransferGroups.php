<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
            ->get();
        $bar = $this->output->createProgressBar($groups->count());
        foreach ($groups as $g) {
            DB::table('groups')->insert([
                'id' => $g->id,
                'program' => Program::fromOld($g->grade_id, $g->subject_id),
                'year' => $g->year,
                'contract_date' => $g->contract_date,
                'lessons_planned' => $g->lessons_planned,
                'created_at' => $g->updated_at,
                'updated_at' => $g->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();
    }
}

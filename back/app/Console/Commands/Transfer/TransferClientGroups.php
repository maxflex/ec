<?php

namespace App\Console\Commands\Transfer;

use App\Utils\MigrationError;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferClientGroups extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:client-groups';
    protected $description = 'Transfer client groups';

    public function handle()
    {
        MigrationError::table()->where('old_table', 'group_contracts')->delete();
        Schema::disableForeignKeyConstraints();
        DB::table('client_groups')->delete();
        $items = DB::connection('egecrm')
            ->table('group_contracts', 'gc')
            ->join('groups as g', 'g.id', '=', 'gc.group_id')
            ->selectRaw("gc.*, g.subject_id, g.grade_id")
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $item) {
            $contractVersionProgram = $this->getContractVersionProgram(
                $item->contract_id,
                $item->grade_id,
                $item->subject_id
            );
            $newId = DB::table('client_groups')->insertGetId([
                'contract_version_program_id' => $contractVersionProgram->id,
                'group_id' => $item->group_id,
            ]);
            if ($contractVersionProgram->error) {
                MigrationError::create(
                    sprintf(
                        'Не удалось получить contract_version_program_id для договора %d (grade_id: %d, subject_id: %d)',
                        $item->contract_id,
                        $item->grade_id,
                        $item->subject_id,
                    ),
                    'client_groups',
                    'group_contracts',
                    $newId,
                    $item->id,
                );
            }
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();
    }
}

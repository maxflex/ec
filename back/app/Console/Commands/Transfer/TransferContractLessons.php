<?php

namespace App\Console\Commands\Transfer;

use App\Enums\ContractLessonStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferContractLessons extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:contract-lessons';
    protected $description = 'Transfer contract lessons';

    public function handle()
    {
        DB::table('contract_lessons')->delete();
        $items = DB::connection('egecrm')
            ->table('lesson_contracts')
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $item) {
            $status = $this->getStatus($item);
            DB::table('contract_lessons')->insert([
                'lesson_id' => $item->lesson_id,
                'contract_id' => $item->contract_id,
                'price' => $item->price,
                'status' => $status->name,
                'minutes_late' => $status === ContractLessonStatus::late && $item->late ? $item->late : null,
                'is_remote' => $item->is_remote,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }

    private function getStatus($item): ContractLessonStatus
    {
        if ($item->is_absent) {
            return ContractLessonStatus::absent;
        }
        if ($item->late > 0) {
            return ContractLessonStatus::late;
        }
        return ContractLessonStatus::present;
    }
}

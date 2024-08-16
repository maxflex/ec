<?php

namespace App\Console\Commands\Transfer;

use App\Enums\ClientLessonStatus;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferClientLessons extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:client-lessons';
    protected $description = 'Transfer contract lessons';

    public function handle()
    {
        DB::table('client_lessons')->delete();
        $items = DB::connection('egecrm')
            ->table('lesson_contracts', 'lc')
            ->join('lessons as l', 'l.id', '=', 'lc.lesson_id')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->selectRaw("lc.*, g.subject_id, g.grade_id")
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $item) {
            $status = $this->getStatus($item);
            try {
                DB::table('client_lessons')->insert([
                    'lesson_id' => $item->lesson_id,
                    'contract_version_program_id' => $this->getContractVersionProgramId(
                        $item->contract_id,
                        $item->grade_id,
                        $item->subject_id
                    ),
                    'price' => $item->price,
                    'status' => $status->name,
                    'minutes_late' => $status === ClientLessonStatus::late && $item->late ? $item->late : null,
                    'is_remote' => $item->is_remote,
                ]);
            } catch (Exception $e) {
                // бывает unique exception (по одному contract_version_program_id в двух одинаковых lesson_id)
            }
            $bar->advance();
        }
        $bar->finish();
    }

    private function getStatus($item): ClientLessonStatus
    {
        if ($item->is_absent) {
            return ClientLessonStatus::absent;
        }
        if ($item->late > 0) {
            return ClientLessonStatus::late;
        }
        return ClientLessonStatus::present;
    }
}

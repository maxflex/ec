<?php

namespace App\Console\Commands\Transfer;

use App\Enums\ClientLessonStatus;
use App\Utils\MigrationError;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferClientLessons extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:client-lessons';
    protected $description = 'Transfer contract lessons';

    public function handle()
    {
        MigrationError::table()->where('old_table', 'lesson_contracts')->delete();
        Schema::disableForeignKeyConstraints();
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
            $contractVersionProgram = $this->getContractVersionProgram(
                $item->contract_id,
                $item->grade_id,
                $item->subject_id
            );
            $scores = $item->score ? json_encode([[
                'score' => $item->score,
                'comment' => $this->nullify($item->score_comment)
            ]]) : null;
            $newId = DB::table('client_lessons')->insertGetId([
                'lesson_id' => $item->lesson_id,
                'contract_version_program_id' => $contractVersionProgram->id,
                'price' => $item->price, // is_free здесь уже правильно учтено
                'status' => $status->name,
                'minutes_late' => $this->getMinutesLate($item, $status),
                'scores' => $scores,
            ]);
            if ($contractVersionProgram->error) {
                MigrationError::create(
                    sprintf(
                        'Не удалось получить contract_version_program_id для договора %d (grade_id: %d, subject_id: %d)',
                        $item->contract_id,
                        $item->grade_id,
                        $item->subject_id,
                    ),
                    'client_lessons',
                    'lesson_contracts',
                    $newId,
                    $item->id,
                );
            }
            // бывает unique exception (по одному contract_version_program_id в двух одинаковых lesson_id)
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();
    }

    private function getStatus($item): ClientLessonStatus
    {
        if ($item->is_absent) {
            return ClientLessonStatus::absent;
        }
        if ($item->late > 0) {
            return $item->is_remote
                ? ClientLessonStatus::lateOnline
                : ClientLessonStatus::late;
        }
        return $item->is_remote
            ? ClientLessonStatus::presentOnline
            : ClientLessonStatus::present;
    }

    private function getMinutesLate($item, ClientLessonStatus $status): ?int
    {
        if (in_array($status, [
            ClientLessonStatus::late,
            ClientLessonStatus::lateOnline
        ])) {
            return $item->late;
        }
        return null;
    }
}

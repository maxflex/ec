<?php

namespace App\Console\Commands\Transfer;

use App\Enums\ClientLessonStatus;
use App\Enums\LessonStatus;
use App\Enums\Program;
use App\Models\Contract;
use App\Models\Group;
use App\Models\Lesson;
use App\Utils\MigrationError;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Для создания нового файла Transfer
 * stub заменить на нужную модель
 */
class TransferPaymentAdditionals extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:payment-additionals';
    protected $description = 'Transfer payment additionals';

    public function handle()
    {
        MigrationError::table()->where('old_table', 'payment_additionals')->delete();
        $subjects = [
            "МАТ" => 1,
            "ФИЗ" => 2,
            "ХИМ" => 3,
            "БИО" => 4,
            "ИНФ" => 5,
            "РУС" => 6,
            "ЛИТ" => 7,
            "ОБЩ" => 8,
            "ИСТ" => 9,
            "АНГ" => 10,
            "ГЕО" => 11
        ];
        $letter = 'X';
        $now = now()->format('Y-m-d H:i:s');

        $groupIds = Group::where('letter', $letter)->pluck('id');
        $lessonIds = Lesson::whereIn('group_id', $groupIds)->pluck('id');

        DB::table('client_lessons')->whereIn('lesson_id', $lessonIds)->delete();
        DB::table('lessons')->whereIn('group_id', $groupIds)->delete();
        DB::table('groups')->whereIn('id', $groupIds)->delete();

        $data = DB::connection('egecrm')
            ->table('payment_additionals')
            ->whereLike('purpose', '%дополнительное%')
            ->where('entity_type', ET_TEACHER)
            ->selectRaw("entity_id, purpose, `year`")
            ->groupByRaw("entity_id, purpose, `year`")
            ->get();

        $bar = $this->output->createProgressBar($data->count());

        foreach ($data as $d) {
            [$subjectGrade, $time] = str($d->purpose)
                ->after("дополнительное занятие (")
                ->before(")")
                ->explode(", ")
                ->all();

            [$subject, $grade] = explode("-", $subjectGrade);
            $gradeId = $grade === "Э" ? 14 : intval($grade);
            $subjectId = $subjects[$subject];
            $program = Program::fromOld($gradeId, $subjectId);

            $groupId = DB::table('groups')->insertGetId([
                'letter' => 'X',
                'program' => $program,
                'year' => $d->year,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $payments = DB::connection('egecrm')
                ->table('payment_additionals')
                ->where('entity_type', ET_TEACHER)
                ->where('year', $d->year)
                ->where('entity_id', $d->entity_id)
                ->where('purpose', $d->purpose)
                ->get();

            foreach ($payments as $payment) {
                $lessonId = DB::table('lessons')->insertGetId([
                    'group_id' => $groupId,
                    'teacher_id' => $d->entity_id,
                    'price' => $payment->sum,
                    'status' => LessonStatus::conducted->value,
                    'cabinet' => null,
                    'quarter' => null,
                    'date' => $payment->date,
                    'time' => $time,
                    'conducted_at' => $payment->date . " $time:00",
                    'topic' => $payment->purpose,
                    'files' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $clientLessons = DB::connection('egecrm')
                    ->table('payment_additionals')
                    ->where('entity_type', ET_CLIENT)
                    ->where('year', $d->year)
                    ->where('entity_id', $d->entity_id)
                    ->where('purpose', $d->purpose)
                    ->get();

                foreach ($clientLessons as $clientLesson) {
                    $contract = Contract::where('client_id', $clientLesson->entity_id)
                        ->where('year', $clientLesson->year)
                        ->firstOrFail();
                    $contractVersionProgram = $this->getContractVersionProgram(
                        $contract->id,
                        $gradeId,
                        $subjectId
                    );
                    $newId = DB::table('client_lessons')->insertGetId([
                        'contract_version_program_id' => $contractVersionProgram->id,
                        'lesson_id' => $lessonId,
                        'price' => $clientLesson->sum,
                        'status' => ClientLessonStatus::present->value,
                        'scores' => null,
                    ]);

                    if ($contractVersionProgram->error) {
                        MigrationError::create(
                            sprintf(
                                'Не удалось получить contract_version_program_id для договора %d (grade_id: %d, subject_id: %d)',
                                $contract->id,
                                $gradeId,
                                $subjectId
                            ),
                            'client_lessons',
                            'payment_additionals',
                            $newId,
                            $clientLesson->id,
                        );
                    }
                }
            }
            $bar->advance();
        }
        $bar->finish();
    }
}

<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use App\Utils\MigrationError;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferReports extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:reports';
    protected $description = 'Transfer reports';

    public function handle()
    {
        MigrationError::table()->where('new_table', 'reports')->delete();
        DB::table('reports')->delete();
        $items = DB::connection('egecrm')
            ->table('reports', 'r')
            // не переносить пустые
            ->whereRaw("not (
                homework_comment is null and homework_score = 0
                and learning_ability_comment is null and learning_ability_score = 0
                and recommendation is null and r.price is null
            )")
            ->selectRaw("r.*, (
                select c.grade_id
                from contracts c
                join contract_versions cv on cv.contract_id = c.id
                join contract_subjects cs on cs.contract_version_id = cv.id
                where c.client_id = r.client_id
                    and c.year = r.year
                    and cs.subject_id = r.subject_id
                limit 1
            ) as contract_grade_id")
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $r) {
            $gradeId = $r->grade_id ?? $r->contract_grade_id;
            $program = $gradeId
                ? Program::fromOld($gradeId, $r->subject_id)
                : Program::izlOther;
            $newId = DB::table('reports')->insertGetId([
                'teacher_id' => $r->teacher_id,
                'client_id' => $r->client_id,
                'year' => $r->year,
                'program' => $program,
                'homework_comment' => $r->homework_comment,
                'cognitive_ability_comment' => $r->learning_ability_comment,
                'knowledge_level_comment' => $r->knowledge_comment,
                'recommendation_comment' => $r->recommendation,
                'grade' => round(($r->homework_score + $r->learning_ability_score + $r->knowledge_score) / 3),
                'is_moderated' => ($r->is_not_moderated + 1) % 2,
                'is_published' => $r->is_available_for_parents,
                'moderated_user_id' => null, // раньше не было
                'price' => $r->price,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ]);
            if (!$gradeId) {
                MigrationError::create(
                    sprintf(
                        'Не удалось определить grade_id для отчёта %d, установлена программа "%s"',
                        $newId,
                        $program->getName(),
                    ),
                    'reports',
                    'reports',
                    $newId,
                    $r->id,
                );
            }
            $bar->advance();
        }
        $bar->finish();
    }
}

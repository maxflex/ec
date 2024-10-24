<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferReports extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:reports';
    protected $description = 'Transfer reports';

    public function handle()
    {
        $noGradeId = [];
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
            if (!$gradeId) {
                $noGradeId[] = $r->id;
                continue;
            }
            DB::table('reports')->insert([
                'teacher_id' => $r->teacher_id,
                'client_id' => $r->client_id,
                'year' => $r->year,
                'program' => Program::fromOld($gradeId, $r->subject_id),
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
            $bar->advance();
        }
        $bar->finish();

        if (count($noGradeId)) {
            $this->line(PHP_EOL);
            $this->error(implode(", ", $noGradeId));
        }
    }
}

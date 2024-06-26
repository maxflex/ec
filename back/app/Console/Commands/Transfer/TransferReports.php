<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferReports extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:reports';
    protected $description = 'Transfer reports';

    public function handle()
    {
        DB::table('reports')->delete();
        $items = DB::connection('egecrm')->table('reports')->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $r) {
            $result = DB::connection('egecrm')->select(<<<SQL
            select c.grade_id
            from groups g
            join lessons l on l.group_id = g.id
            join lesson_contracts lc on lc.lesson_id = l.id
            join contracts c on c.id = lc.contract_id
            where l.teacher_id = ?
                and c.client_id = ?
                and g.subject_id = ?
                and g.year = ?
                and l.status = 'conducted'
                and l.conducted_at < ?
            order by l.conducted_at desc
            limit 1
            SQL, [
                $r->teacher_id,
                $r->client_id,
                $r->subject_id,
                $r->year,
                $r->created_at
            ]);
            DB::table('reports')->insert([
                'teacher_id' => $r->teacher_id,
                'client_id' => $r->client_id,
                'year' => $r->year,
                'program' => count($result)
                    ? Program::getById($result[0]->grade_id, $r->subject_id)
                    : Program::getBySubjectId($r->subject_id),
                'homework_comment' => $r->homework_comment,
                // 'user_id' => $this->getUserId($r->created_email_id),
                'is_moderated' => ($r->is_not_moderated + 1) % 2,
                'is_published' => $r->is_available_for_parents,
                'price' => $r->price,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

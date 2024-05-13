<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Program;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferRequests extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:requests';
    protected $description = 'Transfer requests';

    public function handle()
    {
        DB::table('requests')->delete();
        $requests = DB::connection('egecrm')
            ->table('requests')
            ->orderBy('id', 'desc')
            ->get();
        $bar = $this->output->createProgressBar($requests->count());
        foreach ($requests as $r) {
            DB::table('requests')->insert([
                'id' => $r->id,
                'responsible_user_id' => $r->responsible_admin_id,
                'status' => $r->status,
                'program' => optional($this->getProgram($r))->name,
                'google_id' => $r->google_id,
                'yandex_id' => $r->yandex_id,
                'ip' => $r->ip,
                'comment' => $r->comment,
                'user_id' => $this->getUserId($r->created_email_id),
                'created_at' => $r->created_at === '0000-00-00 00:00:00'
                    ? '1999-01-01 00:00:00' : $r->created_at,
                'updated_at' => $r->updated_at === '0000-00-00 00:00:00'
                    ? '1999-01-01 00:00:00' : $r->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }

    private function getProgram($r): Program | null
    {
        // экстернат
        if ($r->grade_id === 14) {
            return Program::mathExt;
        }
        // студенты, остальные, онлайн или классы 1-7
        if (in_array($r->grade_id, [12, 13, 18]) || $r->grade_id < 8) {
            return null;
        }
        if ($r->comment === 'Старшая школа' || in_array($r->grade_id, [8, 15, 16, 17])) {
            return match ($r->grade_id) {
                8, 15 => Program::mathSchool8,
                9, 16 => Program::mathSchool9,
                10, 17 => Program::mathSchool10,
                default => Program::mathSchool9
            };
        }
        if (!$r->grade_id) {
            return null;
        }
        if (!$r->subjects) {
            return match ($r->grade_id) {
                9 => Program::math9,
                10 => Program::math10,
                11 => Program::math11, // 12 – студенты, 13 - остальные быввает и такое
            };
        }
        $subjects = explode(',', $r->subjects);
        $subjectId = intval($subjects[0]);
        return Program::getById($r->grade_id, $subjectId);
    }
}

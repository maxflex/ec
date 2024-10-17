<?php

namespace App\Console\Commands\Transfer;

use App\Enums\RequestDirection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
                'direction' => optional($this->getDirection($r))->name,
                'google_id' => $r->google_id,
                'yandex_id' => $r->yandex_id,
                'ip' => $r->ip,
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

    private function getDirection($r): ?RequestDirection
    {
        if ($r->comment === 'Старшая школа' || in_array($r->grade_id, [8, 15, 16, 17])) {
            return match ($r->grade_id) {
                8, 15 => RequestDirection::school8,
                9, 16 => RequestDirection::school9,
                10, 17 => RequestDirection::school10,
                default => RequestDirection::school11
            };
        }
        if ($r->comment === 'Развивающие курсы (программирование на Python)') {
            return RequestDirection::otherPython;
        }
        if ($r->comment === 'Развивающие курсы (разговорный английский язык)') {
            return RequestDirection::otherEnglish;
        }

        return match ($r->grade_id) {
            9 => RequestDirection::courses9,
            10 => RequestDirection::courses10,
            11 => RequestDirection::courses11,
            14 => RequestDirection::external,
            default => null
        };
    }
}

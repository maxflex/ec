<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Grade;
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
            ->get();
        $bar = $this->output->createProgressBar($requests->count());
        foreach ($requests as $r) {
            DB::table('requests')->insert([
                'status' => $r->status,
                'grade' => $r->grade_id ? Grade::getById($r->grade_id)->name : null,
                'google_id' => $r->google_id,
                'yandex_id' => $r->yandex_id,
                'ip' => $r->ip,
                'comment' => $r->comment,
                'user_id' => $this->getUserId($r->created_email_id),
                'responsible_user_id' => $r->responsible_admin_id,
                'created_at' => $r->created_at === '0000-00-00 00:00:00'
                    ? '1970-01-01 00:00:00' : $r->created_at,
                'updated_at' => $r->updated_at === '0000-00-00 00:00:00'
                    ? '1970-01-01 00:00:00' : $r->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

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

    protected $phones;

    public function handle()
    {
        DB::table('requests')->delete();
        $requests = DB::connection('egecrm')
            ->table('requests')
            ->orderBy('id', 'desc')
            ->get();
        // $this->phones = DB::connection('egecrm')
        //     ->table('phones')
        //     ->whereIn('entity_type', [
        //         self::CLIENT,
        //         self::PARENT,
        //         self::REQUEST
        //     ])
        //     ->get();
        // $this->phones = key_by($this->phones, 'entity_type', 'entity_id', 'phone');
        // $bar = $this->output->createProgressBar($requests->count());
        foreach ($requests as $r) {
            // $this->getClientId($r);
            DB::table('requests')->insert([
                'id' => $r->id,
                // 'client_id' => $this->getClientId($r),
                'responsible_user_id' => $r->responsible_admin_id,
                'status' => $r->status,
                'grade' => $r->grade_id ? Grade::getById($r->grade_id)->name : null,
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
            // $bar->advance();
        }
        // $bar->finish();
    }

    private function getClientId($r)
    {
        $requestPhones = $this->phones
            ->where('entity_type', self::REQUEST)
            ->where('entity_id', $r->id)
            ->pluck('phone');
        // dd($requestPhones);
        // $requestPhones = $this->phones[self::REQUEST][$r->id];
        $this->line("Request ID: " . $r->id);
        foreach ($requestPhones as $phone) {
            $candidates = $this->phones
                ->whereIn('entity_type', [self::CLIENT, self::PARENT])
                ->where('phone', $phone)
                ->all();
            dump($candidates);
        }
        $this->line(PHP_EOL);
    }
}

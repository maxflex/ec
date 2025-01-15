<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Direction;
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
                'is_verified' => !!$r->created_email_id,
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

    private function getDirection($r): ?Direction
    {
        $comment = str($r->comment ?? '')->lower();
        if ($comment->contains(['пробник', 'пробный'])) {
            return Direction::egeTrial;
        }
        if ($comment->value() === 'онлайн') {
            // снесли
//            return Direction::online;
        }
        if ($comment->contains('старшая школа') || in_array($r->grade_id, [8, 15, 16, 17])) {
            return match ($r->grade_id) {
                8, 15 => Direction::school8,
                9, 16 => Direction::school9,
                10, 17 => Direction::school10,
                default => Direction::school11
            };
        }
        if ($comment->value() === 'развивающие курсы (программирование на python)') {
            // снесли
//            return Direction::python;
        }
        if ($comment->value() === 'развивающие курсы (разговорный английский язык)') {
            // снесли
//            return Direction::english;
        }

        return match ($r->grade_id) {
            9 => Direction::courses9,
            10 => Direction::courses10,
            11 => Direction::courses11,
            14 => Direction::external,
            default => null
        };
    }
}

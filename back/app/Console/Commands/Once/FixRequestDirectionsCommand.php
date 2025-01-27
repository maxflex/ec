<?php

namespace App\Console\Commands\Once;

use App\Enums\Direction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixRequestDirectionsCommand extends Command
{
    protected $signature = 'once:fix-request-directions';

    protected $description = 'Command description';

    public function handle(): void
    {
        $requests = DB::connection('egecrm')
            ->table('requests')
            ->whereRaw("date(`created_at`) between '2021-03-01' and '2024-11-30'")
            ->orderBy('id', 'desc')
            ->get();

        $bar = $this->output->createProgressBar($requests->count());
        foreach ($requests as $r) {
            DB::table('requests')
                ->whereId($r->id)
                ->update([
                    'direction' => $this->getDirection($r)
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

        if ($comment->value() === 'развивающие курсы (программирование на python)') {
            return Direction::coursesExtra;
        }

        if ($comment->value() === 'развивающие курсы (разговорный английский язык)') {
            return Direction::coursesExtra;
        }

        return match ($r->grade_id) {
            9 => Direction::courses9,
            10 => Direction::courses10,
            11 => Direction::courses11,
            14 => Direction::external,
            15 => Direction::school8,
            16 => Direction::school9,
            17 => Direction::school10,
            18 => Direction::school11,
            default => null
        };
    }
}

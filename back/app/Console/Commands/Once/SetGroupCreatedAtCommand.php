<?php

namespace App\Console\Commands\Once;

use App\Models\Group;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetGroupCreatedAtCommand extends Command
{
    protected $signature = 'once:set-group-created-at';

    protected $description = 'created_at / updated_at всех групп поставить дата первого занятия - 21 день';

    public function handle(): void
    {
        $groups = Group::query()->get();
        $bar = $this->output->createProgressBar($groups->count());
        foreach ($groups as $group) {
            $createdAt = $group->lessons()->min(DB::raw("CONCAT(`date`, ' ', `time`) - INTERVAL 21 DAY"));
            Group::whereId($group->id)->update([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}

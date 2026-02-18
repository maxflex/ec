<?php

namespace App\Jobs;

use App\Contracts\HasSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Максимальное время выполнения одной попытки 1 минута.
     * Пересчет расписаний может затрагивать несколько связанных сущностей.
     */
    public int $timeout = 60;

    public function __construct(
        private readonly HasSchedule $entity,
        private readonly int $year,
    ) {}

    public function handle(): void
    {
        $this->entity->updateSchedule($this->year);
    }
}

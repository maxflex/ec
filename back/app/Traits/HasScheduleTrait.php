<?php

namespace App\Traits;

use App\Utils\Teeth;

trait HasScheduleTrait
{
    public function updateSchedule(int $year): bool
    {
        $schedule = $this->schedule ?? [];
        $teeth = $this->getSchedule($year);
        if (count($teeth)) {
            $schedule[$year] = $teeth;
        } elseif (isset($schedule[$year])) {
            unset($schedule[$year]);
        }
        $this->schedule = count($schedule) > 0 ? $schedule : null;

        return $this->save();
    }

    public function getSchedule(int $year): array
    {
        return Teeth::get($this->getScheduleQuery($year));
    }

    public function getSavedSchedule(int $year): array
    {
        $schedule = $this->schedule ?? [];

        if (isset($schedule[$year])) {
            return $schedule[$year];
        }

        return [];
    }
}

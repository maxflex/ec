<?php

namespace App\Traits;

use App\Utils\Teeth;

trait HasScheduleTrait
{
    public function updateSchedule(int $year): bool
    {
        $schedule = $this->schedule ?? [];
        $teeth = $this->getSchedule($year);
        if (count((array) $teeth)) {
            $schedule[$year] = $teeth;
        } elseif (isset($schedule[$year])) {
            unset($schedule[$year]);
        }
        $this->schedule = count($schedule) > 0 ? $schedule : null;

        return $this->save();
    }

    public function getSchedule(int $year): object
    {
        return Teeth::get($this->getScheduleQuery($year));
    }

    public function getSavedSchedule(int $year): object
    {
        $schedule = $this->schedule ?? [];

        if (isset($schedule[$year])) {
            return (object) $schedule[$year];
        }

        return (object) [];
    }
}

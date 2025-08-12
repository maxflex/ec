<?php

namespace App\Contracts;

interface HasSchedule
{
    public function getSchedule(int $year): object;

    public function getSavedSchedule(int $year): object;

    public function updateSchedule(int $year);

    public function getScheduleQuery(int $year);
}

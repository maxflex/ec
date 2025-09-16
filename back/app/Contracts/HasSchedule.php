<?php

namespace App\Contracts;

interface HasSchedule
{
    public function getSchedule(int $year): array;

    public function getSavedSchedule(int $year): array;

    public function updateSchedule(int $year);

    public function getScheduleQuery(int $year);
}

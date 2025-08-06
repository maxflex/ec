<?php

namespace App\Contracts;

interface HasSchedule
{
    /**
     * @param  int|null  $year  У учеников и преподов обязательно указывать год для получения регулярного расписания
     */
    public function getSchedule(?int $year = null): object;
}

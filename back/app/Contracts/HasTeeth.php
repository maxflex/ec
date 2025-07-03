<?php

namespace App\Contracts;

interface HasTeeth
{
    /**
     * @param  int|null  $year  У учеников и преподов обязательно указывать год для получения регулярного расписания
     */
    public function getTeeth(?int $year = null): object;
}

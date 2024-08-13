<?php

namespace App\Contracts;

interface HasTeeth
{
    public function getTeeth(int $year): object;
}
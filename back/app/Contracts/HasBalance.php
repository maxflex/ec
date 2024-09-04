<?php

namespace App\Contracts;

interface HasBalance
{
    public function getBalance(?int $year): array;
}
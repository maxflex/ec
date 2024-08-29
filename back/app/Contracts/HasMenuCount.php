<?php

namespace App\Contracts;

interface HasMenuCount
{
    public static function getMenuCount(): int;
}
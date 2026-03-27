<?php

namespace App\Enums;

enum TeacherBalanceType: string
{
    case normal = 'normal';
    case split = 'split';
    case newNko = 'new';

    public function isSplit(): bool
    {
        return $this === self::split;
    }

    public function isNewNko(): bool
    {
        return $this === self::newNko;
    }
}
